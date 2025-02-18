<?php

namespace App\Model;

use App\Utils\Logs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use mysql_xdevapi\Exception;

class FeedBack extends Model
{
    //定义模型关联的数据表
    protected $table = 'feed_backs';
    //定义主键
    protected $primaryKey = 'id';
    //定义禁止操作时间
<<<<<<< HEAD
    public $timestamps = false;
    protected $casts = [
        'content' => 'json', // 声明json类型
    ];
=======
    public $timestamps = true;

    /**
     * @param $id
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public static function getInfo_echo($id)
    {
        try {
            $data = array();
            $i = 0;
            $info = self::where("to_user_id", $id)->get();
            foreach ($info as $item) {
                $user = User::find($item->from_user_id);
                if ($user == null) $user["name"] = "用户已注销";
                $json_decode = json_decode($item->content);
                $data[$i++] = array(
                    "name" => $json_decode->title,
                    "type" => $item->broadcast,
                    "from" => $user["name"],
                    "to" => "我",
                );
            }
            return $data;
        } catch (\Exception $exception) {
            Logs::logError('获取反馈信息出错：', [$exception->getMessage()]);
            $res = array("code" => 100, "msg" => "false", "data" => "获取反馈信息出错");
            return response()->json($res);
        }

    }
>>>>>>> d53a4e25a6c04ca0a85a60586a01e6d74b522fd1
}
