<?php

namespace App\Http\Controllers\Message;

use App\Http\Message\Requests\FrontEndMsg;
use App\Jobs\Message\SendEmail;
use App\Model\ProjectMember;
use App\Model\User;
use App\Utils\Logs;
use Illuminate\Support\Facades\Auth;
use App\Model\FeedBack;
use App\Http\Controllers\Controller;

class FrontEndMsgController extends Controller
{
    /**@author  niyu
     *发邮件给同一个项目的人
     * @param array $id
     * @param $Info
     * @return bool
     * @throws \Exception
     */
    public function SendMail($id = [], $Info)
    {
        try {
            $x = 0;
            $members_array = [];
            foreach ($id as $project_id) {
                $members_array[$x++] = ProjectMember::get_Info("project_id", $project_id, ["user_id"]);
            }
            $users = null;
            $i = 0;
            foreach ($members_array as $members) {
                foreach ($members as $member) {
                    $user = User::getUserInfo($member->user_id, ["email"])->toArray();//collection->array
                    $users[$i++] = $user[0]["email"];
                }
            }
            $users = Array_unique($users);
            $this->dispatch(new SendEmail($users, $Info));
            return true;
        } catch (\Exception $exception) {
            Logs::logError('发送邮件错误!', [$exception->getMessage()]);
            return false;
        }
    }

    /**
     * @param FrontEndMsg $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function SendMail_All_Back(FrontEndMsg $request)
    {
        try {
            $info = $request;
            $data = new FeedBack;
            $content = [$info->question, $info->title];
            $data->from_user_id = Auth::id();
            $data->to_user_id = 0;//所有人
            $data->interface_id = 0;//所在所有项目
            $data->broadcast = 1;//广播 0（前端） 1（后端） -1（所有）
            $data->content = json_encode($content);
            $data->save();
            $project_ids = ProjectMember::get_Info("user_id", Auth::id(), ["project_id"]);
            $project_id = [];
            foreach ($project_ids as $item) {
                Array_push($project_id, $item->project_id);
            }
            if ($this->SendMail($project_id, $content)) {
                $res = array("code" => 200, "msg" => "邮件已进入队列，正等待处理", "data" => null);
                return response()->json($res);
            } else {
                $res = array("code" => 200, "msg" => "邮件发送出现错误", "data" => null);
                return response()->json($res);
            }
        } catch (\Exception $exception) {
            Logs::logError('创建反馈信息出错：', [$exception->getMessage()]);
            $res = array("code" => 100, "msg" => "创建反馈信息出错", "data" => null);
            return response()->json($res);
        }
    }

    /**
     * @author niyu
     * 获取反馈信息回显
     * @param
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function MyMessage()
    {
        try {
            //$data= FeedBack::getInfo_echo(Auth::id());
            $data = FeedBack::getInfo_echo(Auth::id());
            $res = array("code" => 200, "msg" => "success", "data" => $data);
            return response()->json($res);
        } catch (\Exception $exception) {
            Logs::logError('获取信息出错：', [$exception->getMessage()]);
            $res = array("code" => 100, "msg" => "获取息出错", "data" => null);
            return response()->json($res);
        }
    }
}