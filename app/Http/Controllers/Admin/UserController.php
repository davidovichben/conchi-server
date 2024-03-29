<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProgramDay;
use App\Models\ProgramWeek;
use App\Models\User;
use App\Services\DataTableManager;
use App\Services\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        $query = User::query();

        $columns = ['first_name', 'last_name', 'email', 'mobile', 'payment_package_id', 'created_at'];
        $paginator = DataTableManager::getInstance($query, $request->all(), $columns)->getQuery();

        return $this->dataTableResponse($paginator);
    }

    public function show(User $user)
    {
        $user->paymentPackage;
        $user->details;
        $user->sentences;
        $user->hobbies;

        return response([
            ...$user->toArray(),
            'recorded_name'     => $user->getFile('name', 'webm'),
            'recorded_nickname' => $user->getFile('nickname', 'webm'),
            'prefix_name_1'     => $user->getFile('prefix_name_1', 'mp3'),
            'prefix_name_2'     => $user->getFile('prefix_name_2', 'mp3'),
            'prefix_name_3'     => $user->getFile('prefix_name_3', 'mp3'),
        ], 200);
    }

    public function upload($userId, Request $request)
    {
        $number = $request->post('number');
        $file = $request->post('file');

        (new UploadedFile($file))->store('users/' . $userId . '/prefix_name_' . $number, 'mp3');
    }

    public function deleteFile($userId, Request $request)
    {
        $number = $request->get('number');
        
        Storage::delete('users/' . $userId . '/prefix_name_' . $number . '.mp3');
    }

    public function programWeeks($userId)
    {
        $rows = ProgramWeek::with(['questions' => function($query) use ($userId) {
            $query->leftJoin('user_program_reports as upr', function($query) use ($userId) {
                return $query->on('upr.program_report_question_id', 'program_report_questions.id')->where('upr.user_id', $userId);
            })
            ->leftJoin('program_report_options as pro', 'upr.program_report_option_id', 'pro.id')
            ->selectRaw('program_report_questions.*, pro.content as userOption');
        }])
        ->leftJoin('user_program_weeks as upw', function($query) use ($userId) {
            return $query->on('upw.program_week_id', 'program_weeks.id')->where('upw.user_id', $userId);
        })
        ->get();

        return response($rows, 200);
    }

    public function programDays($userId, Request $request)
    {
        $rows = ProgramDay::with(['interactions' => function ($query) use ($userId)  {
            $query->join('user_interactions as ui', 'ui.interaction_id', 'interactions.id')
                ->selectRaw('interactions.id, interactions.title, ui.liked, ui.status')
                ->where('ui.user_id', $userId);
        }])
        ->where('week_id', $request->get('weekId'))
        ->get();

        return response($rows, 200);
    }

    public function activate(User $user)
    {
        $isActive = $user->is_active ? 0 : 1;
        $user->update(['is_active' => $isActive]);

        return response(['message' => 'User status updated'], 200);
    }
}
