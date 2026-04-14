<?php

namespace App\Http\Controllers;

use App\Models\CharacterAssessmentCategory;
use App\Models\CharacterAssessmentChoice;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\ClassModel;
use App\Models\Subclass;
use App\Models\Teacher;
use App\Models\CharacterAssessmentTimetable;
use App\Models\CharacterAssessmentAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CharacterAssessmentController extends Controller
{
    public function index()
    {
        $schoolID = Session::get('schoolID');
        if (!$schoolID) {
            return redirect()->route('login');
        }

        $categories = CharacterAssessmentCategory::where('schoolID', $schoolID)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $globalChoices = CharacterAssessmentChoice::where('schoolID', $schoolID)
            ->orderBy('weight', 'asc')
            ->get();

        $academicYears = AcademicYear::where('schoolID', $schoolID)->orderBy('academic_yearID', 'desc')->get();
        $terms = Term::where('schoolID', $schoolID)->orderBy('termID', 'desc')->get();
        $classes = ClassModel::where('schoolID', $schoolID)->where('status', 'active')->get();
        $subclasses = Subclass::whereIn('classID', $classes->pluck('classID'))->get();
        $teachers = Teacher::where('schoolID', $schoolID)->where('status', 'active')->get();

        return view('Admin.character_assessment', compact(
            'categories', 
            'globalChoices', 
            'academicYears', 
            'terms', 
            'classes', 
            'subclasses', 
            'teachers'
        ));
    }

    public function store_category(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $request->validate([
                'categories' => 'required|array',
                'categories.*.name' => 'required|string|max:255',
                'categories.*.type' => 'required|in:text,choice'
            ]);

            foreach ($request->categories as $cat) {
                CharacterAssessmentCategory::create([
                    'schoolID' => $schoolID,
                    'name' => $cat['name'],
                    'type' => $cat['type']
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Categories created successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update_category(Request $request, $id)
    {
        try {
            $category = CharacterAssessmentCategory::findOrFail($id);
            $category->update([
                'name' => $request->name,
                'type' => $request->type
            ]);
            return response()->json(['success' => true, 'message' => 'Category updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete_category($id)
    {
        try {
            CharacterAssessmentCategory::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store_choice(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            $request->validate([
                'choices' => 'required|array',
                'choices.*.name' => 'required|string|max:255',
                'choices.*.weight' => 'required|integer'
            ]);

            // Clear existing global choices for this school to perform a sync
            CharacterAssessmentChoice::where('schoolID', $schoolID)->delete();

            foreach ($request->choices as $choice) {
                CharacterAssessmentChoice::create([
                    'schoolID' => $schoolID,
                    'choice_name' => $choice['name'],
                    'weight' => $choice['weight']
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Global choices saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update_choice(Request $request, $id)
    {
        try {
            $choice = CharacterAssessmentChoice::findOrFail($id);
            $choice->update([
                'choice_name' => $request->choice_name,
                'weight' => $request->weight
            ]);
            return response()->json(['success' => true, 'message' => 'Choice updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete_choice($id)
    {
        try {
            CharacterAssessmentChoice::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Choice deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function get_timetables(Request $request)
    {
        try {
            $schoolID = auth()->user()->schoolID ?? Session::get('schoolID');
            if (!$schoolID) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: School identity not found.'], 401);
            }

            $year = $request->year;
            $term = $request->term;
            $id = $request->id;

            $query = CharacterAssessmentTimetable::where('schoolID', $schoolID)
                ->with(['academicYear', 'assignments.class', 'assignments.subclass', 'assignments.teacher']);
            
            if ($id) {
                $query->where('timetableID', $id);
            } else {
                if ($year) $query->where('academic_yearID', $year);
                if ($term) $query->where('term', $term);
            }

            $timetables = $query->orderBy('start_date', 'desc')->get();

            return response()->json(['success' => true, 'timetables' => $timetables]);
        } catch (\Exception $e) {
            \Log::error('CharacterAssessment get_timetables error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    public function store_timetable(Request $request)
    {
        try {
            $schoolID = Session::get('schoolID');
            
            // 1. Create Timetable
            $timetable = CharacterAssessmentTimetable::create([
                'schoolID' => $schoolID,
                'academic_yearID' => $request->academic_yearID,
                'term' => $request->term,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'target_classes' => $request->target_classes // Array of IDs
            ]);

            // 2. Create Assignments
            if ($request->assignments && is_array($request->assignments)) {
                foreach ($request->assignments as $asgn) {
                    CharacterAssessmentAssignment::create([
                        'timetableID' => $timetable->timetableID,
                        'classID' => $asgn['classID'],
                        'subclassID' => $asgn['subclassID'] == 'all' ? null : $asgn['subclassID'],
                        'teacherID' => $asgn['teacherID']
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Timetable defined successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function delete_timetable($id)
    {
        try {
            CharacterAssessmentTimetable::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Timetable deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
