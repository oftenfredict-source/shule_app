/* Black POLER prototype interactions + i18n */
(function () {
  const dict = {
    en: {
      langLabel: "Language",
      printPdf: "Export PDF",
      interactive: "Interactive Mode",
      coverTitle: "Black POLER",
      coverTag: "The House of the Uni Gent.",
      coverDesc:
        "A premium fashion & lifestyle social platform for university gentlemen and style-conscious creatives — where fashion, culture, music, and identity meet.",
      mission: "Mission",
      missionText: "Inspire confidence, class, and creativity across Africa’s leading university fashion community.",
      vision: "Vision",
      visionText: "Become the world’s most influential African fashion lifestyle platform.",
      colors: "Color System",
      typography: "Typography",
      typoText: "Century Gothic — clean, modern, premium.",
      style: "Style",
      styleText: "Luxury × Streetwear × Modern African Fashion",
      secAuth: "Account Opening Flow",
      secAuthSub: "From first launch to a personalized Home Feed.",
      secMain: "Core Experience",
      secMainSub: "Social discovery, creators, and daily engagement.",
      secHubs: "Hubs & Profiles",
      secHubsSub: "Dedicated spaces for brands, artists, and models.",
      secShop: "Marketplace & Commerce",
      secShopSub: "Collections, drops, cart, and checkout.",
      secComm: "Community & Connectivity",
      secCommSub: "Events, messaging, notifications, and settings.",
      footer: "Black POLER UI/UX Prototype · iPhone 16 · Presentation Deck",
      splashTag: "The House of the Uni Gent.",
      getStarted: "Get Started",
      next: "Continue",
      skip: "Skip",
      welcome: "Welcome to Black POLER",
      signUp: "Sign Up",
      logIn: "Log In",
      google: "Continue with Google",
      apple: "Continue with Apple",
      guest: "Continue as Guest",
      createAccount: "Create Account",
      verify: "Verify",
      verifyTitle: "Enter Verification Code",
      verifySub: "We sent a 6-digit code to your email.",
      completeProfile: "Complete Profile",
      interestsTitle: "What inspires your style?",
      interestsSub: "Select interests to personalize your feed.",
      accountType: "Choose Account Type",
      followTitle: "Follow Suggested Accounts",
      home: "Home",
      explore: "Explore",
      market: "Shop",
      profile: "Profile",
      create: "Create",
      search: "Search fashion, creators, brands…",
      forYou: "For You",
      trending: "Trending",
      events: "Events",
      messages: "Messages",
      notifications: "Notifications",
      settings: "Settings",
      cart: "Cart",
      checkout: "Checkout",
      addCart: "Add to Cart",
      buyNow: "Buy Now",
      follow: "Follow",
      following: "Following",
      related: "Related",
    },
    sw: {
      langLabel: "Lugha",
      printPdf: "Pakua PDF",
      interactive: "Hali ya Kuingiliana",
      coverTitle: "Black POLER",
      coverTag: "The House of the Uni Gent.",
      coverDesc:
        "Jukwaa la premium la fashion na lifestyle kwa wanafunzi wa vyuo na wabunifu — fashion, utamaduni, muziki na utambulisho.",
      mission: "Dhamira",
      missionText: "Kuhamasisha ujasiri, class, na ubunifu katika jamii ya fashion ya vyuo Afrika.",
      vision: "Maono",
      visionText: "Kuwa jukwaa lenye ushawishi mkubwa duniani la fashion lifestyle kutoka Afrika.",
      colors: "Mfumo wa Rangi",
      typography: "Herufi",
      typoText: "Century Gothic — safi, ya kisasa, ya premium.",
      style: "Mtindo",
      styleText: "Luxury × Streetwear × Fashion ya Kisasa ya Kiafrika",
      secAuth: "Mtiririko wa Kufungua Akaunti",
      secAuthSub: "Kutoka kufungua app hadi Home Feed iliyobinafsishwa.",
      secMain: "Uzoefu Mkuu",
      secMainSub: "Kugundua, creators, na mwingiliano wa kila siku.",
      secHubs: "Hubs na Profiles",
      secHubsSub: "Nafasi maalum kwa brands, artists, na models.",
      secShop: "Soko na Biashara",
      secShopSub: "Collections, drops, cart, na checkout.",
      secComm: "Jamii na Mawasiliano",
      secCommSub: "Matukio, ujumbe, arifa, na mipangilio.",
      footer: "Black POLER UI/UX Prototype · iPhone 16 · Presentation Deck",
      splashTag: "The House of the Uni Gent.",
      getStarted: "Anza",
      next: "Endelea",
      skip: "Ruka",
      welcome: "Karibu Black POLER",
      signUp: "Jisajili",
      logIn: "Ingia",
      google: "Endelea na Google",
      apple: "Endelea na Apple",
      guest: "Endelea kama Mgeni",
      createAccount: "Tengeneza Akaunti",
      verify: "Thibitisha",
      verifyTitle: "Weka Nambari ya Uthibitisho",
      verifySub: "Tumekutumia code ya tarakimu 6 kwenye email.",
      completeProfile: "Kamilisha Wasifu",
      interestsTitle: "Nini kinachohamasisha style yako?",
      interestsSub: "Chagua ili feed iwe personalized.",
      accountType: "Chagua Aina ya Akaunti",
      followTitle: "Fuata Akaunti Zinazopendekezwa",
      home: "Nyumbani",
      explore: "Gundua",
      market: "Duka",
      profile: "Wasifu",
      create: "Chapisha",
      search: "Tafuta fashion, creators, brands…",
      forYou: "Kwa Ajili Yako",
      trending: "Zinazovuma",
      events: "Matukio",
      messages: "Ujumbe",
      notifications: "Arifa",
      settings: "Mipangilio",
      cart: "Kikapu",
      checkout: "Malipo",
      addCart: "Ongeza Kikapuni",
      buyNow: "Nunua Sasa",
      follow: "Fuata",
      following: "Unafuata",
      related: "Zinazohusiana",
    },
    fr: {
      langLabel: "Langue",
      printPdf: "Exporter PDF",
      interactive: "Mode interactif",
      coverTitle: "Black POLER",
      coverTag: "The House of the Uni Gent.",
      coverDesc:
        "Plateforme premium de mode et lifestyle pour étudiants et créatifs — mode, culture, musique et identité.",
      mission: "Mission",
      missionText: "Inspirer confiance, classe et créativité au sein de la communauté mode universitaire en Afrique.",
      vision: "Vision",
      visionText: "Devenir la plateforme lifestyle mode africaine la plus influente au monde.",
      colors: "Système de couleurs",
      typography: "Typographie",
      typoText: "Century Gothic — épurée, moderne, premium.",
      style: "Style",
      styleText: "Luxe × Streetwear × Mode africaine moderne",
      secAuth: "Parcours d’inscription",
      secAuthSub: "Du premier lancement au fil d’actualité personnalisé.",
      secMain: "Expérience principale",
      secMainSub: "Découverte sociale, créateurs et engagement quotidien.",
      secHubs: "Hubs & profils",
      secHubsSub: "Espaces dédiés aux marques, artistes et mannequins.",
      secShop: "Marketplace",
      secShopSub: "Collections, drops, panier et paiement.",
      secComm: "Communauté",
      secCommSub: "Événements, messages, notifications et réglages.",
      footer: "Black POLER UI/UX Prototype · iPhone 16 · Presentation Deck",
      splashTag: "The House of the Uni Gent.",
      getStarted: "Commencer",
      next: "Continuer",
      skip: "Passer",
      welcome: "Bienvenue sur Black POLER",
      signUp: "S’inscrire",
      logIn: "Se connecter",
      google: "Continuer avec Google",
      apple: "Continuer avec Apple",
      guest: "Continuer en invité",
      createAccount: "Créer un compte",
      verify: "Vérifier",
      verifyTitle: "Entrez le code de vérification",
      verifySub: "Un code à 6 chiffres a été envoyé à votre e-mail.",
      completeProfile: "Compléter le profil",
      interestsTitle: "Qu’est-ce qui inspire votre style ?",
      interestsSub: "Sélectionnez vos centres d’intérêt.",
      accountType: "Choisir le type de compte",
      followTitle: "Suivre des comptes suggérés",
      home: "Accueil",
      explore: "Explorer",
      market: "Boutique",
      profile: "Profil",
      create: "Créer",
      search: "Rechercher mode, créateurs, marques…",
      forYou: "Pour vous",
      trending: "Tendances",
      events: "Événements",
      messages: "Messages",
      notifications: "Notifications",
      settings: "Réglages",
      cart: "Panier",
      checkout: "Paiement",
      addCart: "Ajouter au panier",
      buyNow: "Acheter",
      follow: "Suivre",
      following: "Abonné",
      related: "Associés",
    },
  };

  function applyLang(code) {
    const t = dict[code] || dict.en;
    document.documentElement.lang = code;
    document.querySelectorAll("[data-i18n]").forEach((el) => {
      const key = el.getAttribute("data-i18n");
      if (t[key] != null) el.textContent = t[key];
    });
    document.querySelectorAll("[data-i18n-placeholder]").forEach((el) => {
      const key = el.getAttribute("data-i18n-placeholder");
      if (t[key] != null) el.setAttribute("placeholder", t[key]);
    });
    const label = document.querySelector("[data-i18n-lang-label]");
    if (label) label.textContent = t.langLabel;
  }

  const select = document.getElementById("langSelect");
  if (select) {
    select.addEventListener("change", () => {
      localStorage.setItem("bp_lang", select.value);
      applyLang(select.value);
    });
    const saved = localStorage.getItem("bp_lang") || "en";
    select.value = saved;
    applyLang(saved);
  }

  document.getElementById("btnPrint")?.addEventListener("click", () => {
    window.print();
  });

  // Subtle entrance
  document.querySelectorAll(".iphone").forEach((el, i) => {
    el.style.opacity = "0";
    el.style.transform = "translateY(18px)";
    setTimeout(() => {
      el.style.transition = "opacity 0.55s ease, transform 0.55s ease";
      el.style.opacity = "1";
      el.style.transform = "translateY(0)";
    }, 80 + i * 40);
  });
})();
