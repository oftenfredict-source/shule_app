<?php

namespace App\Services;

use App\Models\AiTrainedQa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AiSystemKnowledgeService
{
    /**
     * Build and return knowledge chunks from public ShuleXpert system content.
     * Never includes private school/student/finance records.
     *
     * @return list<array{id:string,title:string,locale:string,category:string,text:string,keywords?:list<string>}>
     */
    public function allChunks(?string $locale = null): array
    {
        $locales = $locale ? [$locale] : ['en', 'sw'];
        $chunks = [];

        foreach ($locales as $loc) {
            $chunks = array_merge($chunks, $this->trainedChunks($loc));
            $chunks = array_merge($chunks, $this->ownershipChunks($loc));
            $chunks = array_merge($chunks, $this->faqChunks($loc));
            $chunks = array_merge($chunks, $this->modulesChunks($loc));
            $chunks = array_merge($chunks, $this->pricingChunks($loc));
            $chunks = array_merge($chunks, $this->servicesChunks($loc));
            $chunks = array_merge($chunks, $this->aboutChunks($loc));
            $chunks = array_merge($chunks, $this->contactChunks($loc));
            $chunks = array_merge($chunks, $this->howtoChunks($loc));
        }

        return $chunks;
    }

    /**
     * Retrieve the most relevant knowledge snippets for a user question.
     *
     * @return list<array{id:string,title:string,locale:string,category:string,text:string,score:float}>
     */
    public function retrieve(string $question, string $locale = 'en', int $limit = 6): array
    {
        $question = Str::lower(trim($question));
        if ($question === '') {
            return [];
        }

        $normalized = $this->normalizeQuestion($question);
        $terms = $this->expandTerms($this->tokenize($normalized));
        $intent = $this->detectIntent($normalized);
        $preferred = $this->allChunks($locale);
        $fallback = $locale === 'en' ? [] : $this->allChunks('en');
        $pool = array_merge($preferred, $fallback);

        $scored = [];
        foreach ($pool as $chunk) {
            $title = Str::lower((string) ($chunk['title'] ?? ''));
            $keywords = array_map('strval', $chunk['keywords'] ?? []);
            $hay = $title.' '.Str::lower((string) ($chunk['text'] ?? '')).' '.($chunk['category'] ?? '').' '.implode(' ', $keywords);
            $score = 0.0;

            foreach ($terms as $term) {
                if ($term === '') {
                    continue;
                }
                if (str_contains($hay, $term)) {
                    $score += 3.0;
                    if (str_contains($title, $term)) {
                        $score += 5.0;
                    }
                    // Keyword synonym hits are strong signals for trained Q&A.
                    foreach ($keywords as $kw) {
                        if ($kw !== '' && (str_contains($kw, $term) || str_contains($term, $kw))) {
                            $score += 4.0;
                            break;
                        }
                    }
                }
            }

            if (str_contains($hay, $question) || str_contains($hay, $normalized)) {
                $score += 12.0;
            }

            if (($chunk['category'] ?? '') === 'trained') {
                $score += $this->trainedSimilarityBoost($normalized, $terms, $chunk);
            }

            $score += $this->intentBoost($intent, $chunk);

            if ($score > 0) {
                $chunk['score'] = $score;
                $scored[] = $chunk;
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        $unique = [];
        foreach ($scored as $item) {
            $id = $item['id'];
            if (!isset($unique[$id])) {
                $unique[$id] = $item;
            }
        }

        $hits = array_slice(array_values($unique), 0, $limit);

        // Drop weak noise when a strong top hit exists.
        if ($hits !== [] && ($hits[0]['score'] ?? 0) >= 15) {
            $top = $hits[0]['score'];
            $hits = array_values(array_filter($hits, fn ($h) => $h['score'] >= max(8.0, $top * 0.45)));
        }

        return $hits;
    }

    /**
     * Build a grounded local answer from retrieved chunks (no OpenAI required).
     */
    public function answerFromKnowledge(string $question, string $locale = 'en'): string
    {
        $hits = $this->retrieve($question, $locale, 5);
        if ($hits === []) {
            return $locale === 'sw'
                ? 'Samahani, sina taarifa za kutosha kwenye nyaraka za ShuleXpert kwa swali hilo. Tafadhali wasiliana na EmCa Techonologies: '.config('shulexpert.phone_display', '+255 749 719 998').' au emca@emcatech.co.tz.'
                : 'Sorry, I do not have enough ShuleXpert documentation for that question. Please contact EmCa Techonologies: '.config('shulexpert.phone_display', '+255 749 719 998').' or emca@emcatech.co.tz.';
        }

        $intent = $this->detectIntent(Str::lower(trim($question)));
        $top = $hits[0];

        $footer = $locale === 'sw'
            ? 'Ikiwa unahitaji msaada wa moja kwa moja: EmCa Techonologies, Moshi — '.config('shulexpert.phone_display', '+255 749 719 998').' / emca@emcatech.co.tz.'
            : 'For direct help: EmCa Techonologies, Moshi — '.config('shulexpert.phone_display', '+255 749 719 998').' / emca@emcatech.co.tz.';

        // SuperAdmin-trained common Q&A always wins when it is the top hit.
        if (($top['category'] ?? '') === 'trained') {
            return $top['text']."\n\n".$footer;
        }

        // Ownership / company questions: answer directly from the best ownership fact.
        if ($intent === 'ownership' || str_starts_with((string) $top['id'], 'ownership-') || (($top['category'] ?? '') === 'ownership')) {
            return $top['text']."\n\n".$footer;
        }

        // Login / how-to: answer the matched guide directly.
        if ($intent === 'login' || $intent === 'howto' || ($top['category'] ?? '') === 'howto') {
            return $top['title']."\n".$top['text']."\n\n".$footer;
        }

        // Strong single match: reply with that fact, not a dump of unrelated modules.
        if (count($hits) === 1 || ($top['score'] ?? 0) >= (($hits[1]['score'] ?? 0) + 8)) {
            return $top['title']."\n".$top['text']."\n\n".$footer;
        }

        $lines = [];
        $lines[] = $locale === 'sw'
            ? 'Kulingana na nyaraka za ShuleXpert:'
            : 'Based on ShuleXpert system documentation:';

        // Cap to top 3 related hits only.
        foreach (array_slice($hits, 0, 3) as $i => $hit) {
            $n = $i + 1;
            $lines[] = $n.'. '.$hit['title'];
            $lines[] = $hit['text'];
        }

        $lines[] = '';
        $lines[] = $locale === 'sw'
            ? 'Ikiwa unahitaji msaada wa moja kwa moja: EmCa Techonologies, Moshi — '.config('shulexpert.phone_display', '+255 749 719 998').' / emca@emcatech.co.tz.'
            : 'For direct help: EmCa Techonologies, Moshi — '.config('shulexpert.phone_display', '+255 749 719 998').' / emca@emcatech.co.tz.';

        return implode("\n", $lines);
    }

    /**
     * Compact context block for OpenAI instructions/input.
     */
    public function contextBlock(string $question, string $locale = 'en', int $limit = 6): string
    {
        $hits = $this->retrieve($question, $locale, $limit);
        if ($hits === []) {
            return 'No matching local ShuleXpert documentation was found for this question.';
        }

        $parts = ['ShuleXpert local knowledge (use only these facts):'];
        foreach ($hits as $hit) {
            $parts[] = "### {$hit['title']} [{$hit['category']}]\n{$hit['text']}";
        }

        return implode("\n\n", $parts);
    }

    /**
     * SuperAdmin-trained common questions (highest priority knowledge).
     *
     * @return list<array{id:string,title:string,locale:string,category:string,text:string,keywords:list<string>,priority:int}>
     */
    private function trainedChunks(string $locale): array
    {
        try {
            if (!Schema::hasTable('ai_trained_qa')) {
                return [];
            }
        } catch (\Throwable) {
            return [];
        }

        $cacheKey = 'ai_trained_qa_chunks_'.$locale;

        return Cache::remember($cacheKey, 60, function () use ($locale) {
            $rows = AiTrainedQa::query()
                ->where('is_active', true)
                ->where(function ($q) use ($locale) {
                    $q->where('locale', $locale)->orWhere('locale', 'both');
                })
                ->orderByDesc('priority')
                ->get();

            $chunks = [];
            foreach ($rows as $row) {
                $chunks[] = [
                    'id' => 'trained-'.$row->id,
                    'title' => $row->question,
                    'locale' => $locale,
                    'category' => 'trained',
                    'keywords' => $row->keywordList(),
                    'priority' => (int) $row->priority,
                    'text' => $row->answer,
                ];
            }

            return $chunks;
        });
    }

    /**
     * @return list<array{id:string,title:string,locale:string,category:string,text:string,keywords:list<string>}>
     */
    private function ownershipChunks(string $locale): array
    {
        $phone = (string) config('shulexpert.phone_display', '+255 749 719 998');

        if ($locale === 'sw') {
            return [[
                'id' => 'ownership-sw',
                'title' => 'Nani anamiliki / ametengeneza ShuleXpert?',
                'locale' => 'sw',
                'category' => 'ownership',
                'keywords' => ['mmiliki', 'umiliki', 'owner', 'own', 'owns', 'built', 'developer', 'kampuni', 'company', 'emca', 'nani', 'who'],
                'text' => "ShuleXpert inamilikiwa, imeundwa, na inaendeshwa na EmCa Techonologies (Moshi, Kilimanjaro, Tanzania). Si ya shule binafsi; ni jukwaa la EmCa linalotolewa kwa shule. Mawasiliano: {$phone} au emca@emcatech.co.tz.",
            ]];
        }

        return [[
            'id' => 'ownership-en',
            'title' => 'Who owns / built ShuleXpert?',
            'locale' => 'en',
            'category' => 'ownership',
            'keywords' => ['owner', 'own', 'owns', 'ownership', 'built', 'builder', 'developer', 'company', 'maker', 'who', 'emca', 'belong'],
            'text' => "ShuleXpert is owned, built, and maintained by EmCa Techonologies (Moshi, Kilimanjaro, Tanzania). It is not owned by an individual school; EmCa Techonologies is the company behind the product. Contact: {$phone} or emca@emcatech.co.tz.",
        ]];
    }

    /**
     * @return list<array{id:string,title:string,locale:string,category:string,text:string}>
     */
    private function faqChunks(string $locale): array
    {
        $faq = $this->langArray($locale, 'faq');
        $chunks = [];
        for ($i = 1; $i <= 20; $i++) {
            $q = $faq['q'.$i] ?? null;
            $a = $faq['a'.$i] ?? null;
            if (!$q || !$a) {
                continue;
            }
            $a = str_replace(':phone', (string) config('shulexpert.phone_display', '+255 749 719 998'), $a);
            $chunks[] = [
                'id' => "faq-{$locale}-{$i}",
                'title' => $q,
                'locale' => $locale,
                'category' => 'faq',
                'text' => $a,
            ];
        }

        return $chunks;
    }

    /**
     * @return list<array{id:string,title:string,locale:string,category:string,text:string}>
     */
    private function modulesChunks(string $locale): array
    {
        $modules = $this->langArray($locale, 'modules');
        $chunks = [];
        foreach ($modules as $key => $row) {
            if (!is_array($row) || empty($row['title'])) {
                continue;
            }
            $text = trim(($row['short'] ?? '').' '.($row['desc'] ?? ''));
            if ($text === '') {
                $text = (string) $row['title'];
            }
            $chunks[] = [
                'id' => "module-{$locale}-{$key}",
                'title' => (string) $row['title'],
                'locale' => $locale,
                'category' => 'module',
                'text' => $text,
            ];
        }

        return $chunks;
    }

    /**
     * @return list<array{id:string,title:string,locale:string,category:string,text:string}>
     */
    private function pricingChunks(string $locale): array
    {
        $p = $this->langArray($locale, 'pricing');
        $chunks = [];

        $chunks[] = [
            'id' => "pricing-{$locale}-overview",
            'title' => $p['title'] ?? 'Pricing',
            'locale' => $locale,
            'category' => 'pricing',
            'text' => trim(($p['lead'] ?? '').' Installation: TZS 1,000,000 one-time. Monthly: TZS 500 per active student. '.strip_tags($p['cost_formula'] ?? '')),
        ];

        for ($i = 1; $i <= 6; $i++) {
            $label = $p['inst'.$i.'_label'] ?? null;
            $text = $p['inst'.$i.'_text'] ?? null;
            if ($label && $text) {
                $chunks[] = [
                    'id' => "pricing-{$locale}-inst{$i}",
                    'title' => 'Installation includes: '.$label,
                    'locale' => $locale,
                    'category' => 'pricing',
                    'text' => $text,
                ];
            }
            $mlabel = $p['mon'.$i.'_label'] ?? null;
            $mtext = $p['mon'.$i.'_text'] ?? null;
            if ($mlabel && $mtext) {
                $chunks[] = [
                    'id' => "pricing-{$locale}-mon{$i}",
                    'title' => 'Monthly includes: '.$mlabel,
                    'locale' => $locale,
                    'category' => 'pricing',
                    'text' => $mtext,
                ];
            }
        }

        for ($i = 1; $i <= 5; $i++) {
            $q = $p['faq'.$i.'_q'] ?? null;
            $a = $p['faq'.$i.'_a'] ?? null;
            if ($q && $a) {
                $chunks[] = [
                    'id' => "pricing-{$locale}-faq{$i}",
                    'title' => $q,
                    'locale' => $locale,
                    'category' => 'pricing',
                    'text' => $a,
                ];
            }
        }

        return $chunks;
    }

    /**
     * @return list<array{id:string,title:string,locale:string,category:string,text:string}>
     */
    private function servicesChunks(string $locale): array
    {
        $s = $this->langArray($locale, 'services');
        $chunks = [];
        $chunks[] = [
            'id' => "services-{$locale}-lead",
            'title' => $s['title'] ?? 'Services',
            'locale' => $locale,
            'category' => 'services',
            'text' => trim(($s['lead'] ?? '').' '.($s['intro_text'] ?? '')),
        ];

        for ($i = 1; $i <= 6; $i++) {
            $title = $s['svc'.$i.'_title'] ?? ($s['pillar'.$i.'_title'] ?? null);
            $text = $s['svc'.$i.'_text'] ?? ($s['pillar'.$i.'_text'] ?? null);
            if ($title && $text) {
                $extra = trim(implode(' ', array_filter([
                    $s['svc'.$i.'_p1'] ?? null,
                    $s['svc'.$i.'_p2'] ?? null,
                    $s['svc'.$i.'_p3'] ?? null,
                ])));
                $chunks[] = [
                    'id' => "services-{$locale}-{$i}",
                    'title' => $title,
                    'locale' => $locale,
                    'category' => 'services',
                    'text' => trim($text.' '.$extra),
                ];
            }
        }

        return $chunks;
    }

    /**
     * @return list<array{id:string,title:string,locale:string,category:string,text:string}>
     */
    private function aboutChunks(string $locale): array
    {
        $a = $this->langArray($locale, 'about');
        if ($a === []) {
            return [];
        }

        $keys = ['lead', 'who_p1', 'who_p2', 'partner_text', 'pillar3_text', 'value2_text'];
        $textParts = [];
        foreach ($keys as $key) {
            if (!empty($a[$key])) {
                $textParts[] = strip_tags((string) $a[$key]);
            }
        }

        return [[
            'id' => "about-{$locale}",
            'title' => $a['title'] ?? 'About ShuleXpert',
            'locale' => $locale,
            'category' => 'about',
            'keywords' => ['about', 'emca', 'company', 'built', 'owner', 'who'],
            'text' => trim(implode(' ', $textParts)) ?: 'ShuleXpert is a school management system by EmCa Techonologies.',
        ]];
    }

    /**
     * @return list<array{id:string,title:string,locale:string,category:string,text:string}>
     */
    private function contactChunks(string $locale): array
    {
        $phone = (string) config('shulexpert.phone_display', '+255 749 719 998');
        $email = 'emca@emcatech.co.tz';

        return [[
            'id' => "contact-{$locale}",
            'title' => $locale === 'sw' ? 'Mawasiliano' : 'Contact support',
            'locale' => $locale,
            'category' => 'support',
            'text' => $locale === 'sw'
                ? "EmCa Techonologies, Moshi, Kilimanjaro. Simu: {$phone}. Barua pepe: {$email}."
                : "EmCa Techonologies, Moshi, Kilimanjaro. Phone: {$phone}. Email: {$email}.",
        ]];
    }

    /**
     * Practical how-to guidance grounded in real ShuleXpert modules/roles.
     *
     * @return list<array{id:string,title:string,locale:string,category:string,text:string}>
     */
    private function howtoChunks(string $locale): array
    {
        if ($locale === 'sw') {
            return [
                [
                    'id' => 'howto-sw-login',
                    'title' => 'Jinsi ya kuingia ShuleXpert',
                    'locale' => 'sw',
                    'category' => 'howto',
                    'keywords' => ['login', 'log in', 'signin', 'sign in', 'kuingia', 'ingia', 'nenosiri', 'password', 'username'],
                    'text' => 'Fungua ukurasa wa /login, weka jina la mtumiaji (username) na nenosiri (password) ya shule yako, kisha bofya Login. Admin, Mwalimu, na Staff wana dashibodi tofauti baada ya kuingia. Wazazi hutumia Parent Portal; wanafunzi hutumia Student Portal. Ikiwa umesahau nenosiri, wasiliana na admin wa shule au EmCa Techonologies.',
                ],
                [
                    'id' => 'howto-sw-fees',
                    'title' => 'Jinsi ya kusimamia ada',
                    'locale' => 'sw',
                    'category' => 'howto',
                    'text' => 'Admin anaweza kuweka muundo wa ada, kutengeneza control numbers, kufuatilia malipo, na kutuma SMS za ukumbusho kwa wazazi kupitia moduli ya Fees & Payments.',
                ],
                [
                    'id' => 'howto-sw-attendance',
                    'title' => 'Jinsi ya kutumia mahudhurio / biometric',
                    'locale' => 'sw',
                    'category' => 'howto',
                    'text' => 'ShuleXpert inaunga mkono mahudhurio ya mfumo na vifaa vya fingerprint vya ZKTeco kupitia School Agent. Wazazi wanaweza kupata arifa za SMS za mahudhurio.',
                ],
                [
                    'id' => 'howto-sw-apply',
                    'title' => 'Jinsi ya kuomba shule mtandaoni',
                    'locale' => 'sw',
                    'category' => 'howto',
                    'text' => 'Fungua ukurasa wa Maombi Mtandaoni, tafuta shule, angalia nafasi za madarasa, fungua akaunti, kisha wasilisha maombi. Ni bure kwa wazazi/walezi.',
                ],
                [
                    'id' => 'howto-sw-ai',
                    'title' => 'ShuleXpert AI Assistant ni nini?',
                    'locale' => 'sw',
                    'category' => 'howto',
                    'text' => 'Ni msaidizi wa msaada ndani ya ShuleXpert anayejibu maswali kutoka nyaraka za mfumo (FAQ, moduli, bei, huduma). Haoni data binafsi za wanafunzi, ada za shule, au taarifa za ndani za shule.',
                ],
            ];
        }

        return [
            [
                'id' => 'howto-en-login',
                'title' => 'How to log in to ShuleXpert',
                'locale' => 'en',
                'category' => 'howto',
                'keywords' => ['login', 'log in', 'signin', 'sign in', 'password', 'username', 'access', 'enter'],
                'text' => 'Open the /login page, enter your school username and password, then click Login. Admin, Teacher, and Staff each get role-based dashboards after login. Parents use the Parent Portal; students use the Student Portal. If you forgot your password, contact your school admin or EmCa Techonologies.',
            ],
            [
                'id' => 'howto-en-fees',
                'title' => 'How to manage fees',
                'locale' => 'en',
                'category' => 'howto',
                'text' => 'Admins can configure fee structures, generate control numbers, track payments, and send parent SMS reminders using the Fees & Payments module.',
            ],
            [
                'id' => 'howto-en-attendance',
                'title' => 'How attendance / biometric works',
                'locale' => 'en',
                'category' => 'howto',
                'text' => 'ShuleXpert supports system attendance and ZKTeco fingerprint devices via School Agent sync. Parents can receive attendance SMS alerts.',
            ],
            [
                'id' => 'howto-en-apply',
                'title' => 'How to apply to a school online',
                'locale' => 'en',
                'category' => 'howto',
                'text' => 'Open Online Application, search schools, check class spaces, create an account, then submit an application. Free for parents/guardians.',
            ],
            [
                'id' => 'howto-en-ai',
                'title' => 'What is ShuleXpert AI Assistant?',
                'locale' => 'en',
                'category' => 'howto',
                'text' => 'It is an in-product support assistant that answers from ShuleXpert system documentation (FAQ, modules, pricing, services). It cannot see private student, fee, or school internal records.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function langArray(string $locale, string $file): array
    {
        $path = base_path("lang/{$locale}/{$file}.php");
        if (!File::isFile($path)) {
            return [];
        }

        $data = include $path;

        return is_array($data) ? $data : [];
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $text): array
    {
        $text = Str::lower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text) ?? $text;
        $parts = preg_split('/\s+/', $text) ?: [];
        $stop = [
            'the', 'a', 'an', 'and', 'or', 'to', 'of', 'in', 'for', 'is', 'are', 'was', 'were',
            'how', 'what', 'when', 'where', 'which', 'this', 'that', 'these', 'those', 'with',
            'from', 'does', 'can', 'will', 'have', 'has', 'had', 'been', 'being', 'about',
            'please', 'tell', 'me', 'you', 'your', 'our', 'their', 'any', 'some',
            // Too generic — matches almost every pricing/module snippet.
            'system', 'systems', 'mfumo', 'software', 'platform',
            'ya', 'na', 'kwa', 'ni', 'je', 'za', 'la', 'cha', 'wa', 'wenye', 'hii', 'hiyo', 'hizo',
            'nini', 'gani', 'basi', 'sasa', 'pia', 'au', 'kutoka', 'jinsi',
        ];

        return array_values(array_unique(array_filter($parts, function ($p) use ($stop) {
            return mb_strlen($p) >= 3 && !in_array($p, $stop, true);
        })));
    }

    /**
     * Normalize informal / paraphrased questions before matching.
     */
    private function normalizeQuestion(string $question): string
    {
        $q = Str::lower(trim($question));
        $q = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $q) ?? $q;
        $q = preg_replace('/\s+/u', ' ', $q) ?? $q;

        $replacements = [
            '/\bwhat is this (system|software|platform|app|product)\b/u' => 'what is shulexpert',
            '/\bwhat is (the )?(system|software|platform|app|product) about\b/u' => 'what is shulexpert',
            '/\b(tell me about|explain|describe) (this )?(system|software|platform|shulexpert)\b/u' => 'what is shulexpert',
            '/\b(mfumo|system) huu ni nini\b/u' => 'shulexpert ni nini',
            '/\bni nini (hii|huyo|huu)?\s*(mfumo|system)?\b/u' => 'shulexpert ni nini',
            '/\bnani (amemiliki|anamiliki|alitengeneza|alijenga|aliunda)\b/u' => 'nani alitengeneza shulexpert',
            '/\bwho (owns?|built|made|created|developed) (this )?(system|software|platform|app)?\b/u' => 'who developed shulexpert',
            '/\b(bei|gharama).*(ufungaji|installation)\b/u' => 'gharama ya ufungaji',
            '/\b(ufungaji|installation).*(bei|gharama|cost|price|ngapi)\b/u' => 'gharama ya ufungaji',
            '/\b(ada|gharama|bei).*(mwezi|monthly|subscription)\b/u' => 'ada ya usajili wa kila mwezi',
            '/\b(monthly|subscription).*(fee|cost|price|bei)\b/u' => 'monthly subscription fee',
            '/\b(siwezi|cannot|cant|can t|unable).*(login|log in|kuingia|ingia)\b/u' => 'siwezi kuingia shulexpert',
            '/\b(nataka|ninaweza|how (do|can) i).*(control numbers?)\b/u' => 'can i generate control numbers',
            '/\b(parents?).*(see|view|access|check).*(result|results|matokeo)\b/u' => 'can parents access student results',
            '/\b(ina|does it|do you).*(fingerprint|biometric)\b/u' => 'fingerprint attendance',
            '/\b(ina|does it).*(hostel|hosteli)\b/u' => 'hostel management',
            '/\b(lesson plans?|mpango wa somo).*(inawezekana|possible|support|create)?\b/u' => 'can teachers create lesson plans',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $q = preg_replace($pattern, $replacement, $q) ?? $q;
        }

        return trim($q);
    }

    /**
     * Fuzzy score for SuperAdmin-trained Q&A against paraphrased user questions.
     *
     * @param  list<string>  $terms
     * @param  array{title?:string,text?:string,keywords?:list<string>,priority?:int}  $chunk
     */
    private function trainedSimilarityBoost(string $normalizedQuestion, array $terms, array $chunk): float
    {
        $title = Str::lower((string) ($chunk['title'] ?? ''));
        $keywords = array_map(static fn ($k) => Str::lower((string) $k), $chunk['keywords'] ?? []);
        $titleNorm = $this->normalizeQuestion($title);
        $titleTerms = $this->tokenize($titleNorm);
        $boost = 0.0;

        similar_text($normalizedQuestion, $titleNorm, $pct);
        if ($pct >= 28) {
            $boost += ($pct / 100) * 42;
        }

        if ($terms !== [] && $titleTerms !== []) {
            $overlap = count(array_intersect($terms, $titleTerms));
            $union = count(array_unique(array_merge($terms, $titleTerms)));
            if ($union > 0 && $overlap > 0) {
                $jaccard = $overlap / $union;
                $boost += $jaccard * 48;
                $boost += $overlap * 6;
            }
        }

        $kwHits = 0;
        foreach ($keywords as $kw) {
            if ($kw === '') {
                continue;
            }
            if (str_contains($normalizedQuestion, $kw)) {
                $kwHits++;
                continue;
            }
            foreach ($terms as $term) {
                if (mb_strlen($term) < 3) {
                    continue;
                }
                if (str_contains($kw, $term) || str_contains($term, $kw)) {
                    $kwHits++;
                    break;
                }
            }
        }
        if ($kwHits > 0) {
            $boost += min(28.0, $kwHits * 9.0);
        }

        if ($boost >= 8) {
            $boost += (float) (($chunk['priority'] ?? 0) * 0.6);
        }

        return $boost;
    }

    /**
     * Expand query terms with common synonyms (login ↔ log in, etc.).
     *
     * @param  list<string>  $terms
     * @return list<string>
     */
    private function expandTerms(array $terms): array
    {
        $extra = [];
        $joined = implode(' ', $terms);

        if (preg_match('/\b(login|signin|sign|kuingia|ingia)\b/u', $joined)
            || in_array('log', $terms, true)
        ) {
            $extra = array_merge($extra, ['login', 'log in', 'signin', 'sign in', 'kuingia', 'password', 'username', 'siwezi']);
        }

        if (preg_match('/\b(fee|fees|ada|malipo|payment|payments|bei|gharama|ufungaji|installation|subscription)\b/u', $joined)) {
            $extra = array_merge($extra, ['fees', 'ada', 'payment', 'pricing', 'installation', 'subscription', 'bei', 'gharama']);
        }

        if (preg_match('/\b(shulexpert)\b/u', $joined)) {
            $extra = array_merge($extra, ['what is shulexpert', 'shulexpert ni nini', 'school management']);
        }

        if (preg_match('/\b(control|number|numbers)\b/u', $joined)) {
            $extra = array_merge($extra, ['control number', 'control numbers']);
        }

        if (preg_match('/\b(fingerprint|biometric|zkteco)\b/u', $joined)) {
            $extra = array_merge($extra, ['fingerprint', 'biometric', 'attendance', 'mahudhurio']);
        }

        if (preg_match('/\b(hostel|hosteli|accommodation)\b/u', $joined)) {
            $extra = array_merge($extra, ['hostel', 'accommodation', 'hosteli']);
        }

        if (preg_match('/\b(lesson|plan|plans)\b/u', $joined)) {
            $extra = array_merge($extra, ['lesson plan', 'lesson plans']);
        }

        if (preg_match('/\b(parent|parents|wazazi)\b/u', $joined) && preg_match('/\b(result|results|matokeo)\b/u', $joined)) {
            $extra = array_merge($extra, ['parent portal', 'student results', 'matokeo']);
        }

        return array_values(array_unique(array_merge($terms, $extra)));
    }

    private function detectIntent(string $question): ?string
    {
        $q = Str::lower($question);

        if (preg_match('/\b(what is shulexpert|shulexpert ni nini)\b/u', $q)) {
            return 'about_product';
        }

        if (preg_match('/\b(own|owns|owner|ownership|built|builder|developer|company|maker|belong|mmiliki|umiliki|ametengeneza|anamiliki|amemiliki|kampuni)\b/u', $q)
            || preg_match('/\bwho\s+(owns?|built|made|created|developed)\b/u', $q)
            || preg_match('/\bnani\s+(anamiliki|amemiliki|ametengeneza|alijenga)\b/u', $q)
        ) {
            return 'ownership';
        }

        if (preg_match('/\b(log[\s-]?in|login|sign[\s-]?in|signin|kuingia|ingia|nenosiri|password|username)\b/u', $q)) {
            return 'login';
        }

        if (preg_match('/\b(price|pricing|cost|bei|gharama|malipo|subscription|ufungaji|installation fee|monthly)\b/u', $q)) {
            return 'pricing';
        }

        if (preg_match('/\b(contact|support|phone|simu|barua|email|msaada|wasiliana)\b/u', $q)) {
            return 'contact';
        }

        if (preg_match('/\b(how\s+to|jinsi\s+ya)\b/u', $q)) {
            return 'howto';
        }

        return null;
    }

    /**
     * @param  array{id:string,title:string,locale:string,category:string,text:string,keywords?:list<string>}  $chunk
     */
    private function intentBoost(?string $intent, array $chunk): float
    {
        if ($intent === null) {
            return 0.0;
        }

        $category = $chunk['category'] ?? '';
        $id = $chunk['id'] ?? '';

        // Light lift for trained answers; similarity boost does the heavy matching.
        $trainedLift = $category === 'trained' ? 6.0 : 0.0;
        $title = Str::lower((string) ($chunk['title'] ?? ''));

        $intentScore = match ($intent) {
            'about_product' => match (true) {
                $category === 'trained' && preg_match('/\b(what is shulexpert|shulexpert ni nini)\b/u', $title) => 55.0,
                $category === 'about' => 20.0,
                $category === 'trained' => -10.0,
                default => -4.0,
            },
            'ownership' => match (true) {
                $category === 'ownership', str_starts_with($id, 'ownership-') => 40.0,
                $category === 'trained' && preg_match('/\b(developed|who|nani|alitengeneza|emca)\b/u', $title) => 40.0,
                $category === 'trained' => 10.0,
                $category === 'about' => 18.0,
                $category === 'faq' && str_contains($title.' '.Str::lower((string) ($chunk['text'] ?? '')), 'emca')
                    && preg_match('/\b(built|powered|who|nani|emca)\b/u', $title) => 22.0,
                default => -6.0,
            },
            'login' => match (true) {
                str_contains($id, 'login'), str_contains($id, '-login') => 40.0,
                $category === 'trained' && preg_match('/\b(log ?in|kuingia|siwezi)\b/u', $title) => 40.0,
                $category === 'trained' => 12.0,
                $category === 'howto' && str_contains($title.' '.Str::lower((string) ($chunk['text'] ?? '')), 'login') => 35.0,
                $category === 'howto' => 8.0,
                default => -8.0,
            },
            'howto' => match (true) {
                $category === 'howto' => 20.0,
                $category === 'trained' => 18.0,
                $category === 'pricing' => -6.0,
                default => 0.0,
            },
            'pricing' => match (true) {
                $category === 'pricing' => 20.0,
                $category === 'trained' && preg_match('/\b(price|pricing|cost|bei|gharama|installation|subscription|usajili|ufungaji|monthly)\b/u', $title) => 35.0,
                $category === 'trained' => 8.0,
                $category === 'module' => -4.0,
                default => 0.0,
            },
            'contact' => in_array($category, ['support', 'ownership', 'trained'], true) ? 16.0 : 0.0,
            default => 0.0,
        };

        return $trainedLift + $intentScore;
    }
}
