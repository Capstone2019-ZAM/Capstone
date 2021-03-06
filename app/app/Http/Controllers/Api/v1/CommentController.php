<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CreateRequest;
use App\Comment;
use App\Repositories\ModelRepository;
use App\User;
use App\Issue;
use Exception;
use Illuminate\Http\Request;
//TODO Remove this import and update eloquent query to repository method
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class CommentController extends Controller
{
    protected $model_comment;
    protected $model_user;
    protected $model_issue;

    public function __construct(Comment $comment, User $user ,Issue $issue)
    {
        $this->model_comment = new ModelRepository($comment);
        $this->model_user = new ModelRepository($user);
        $this->model_issue = new ModelRepository($issue);

    }

    public function get($id)
    {
        $result = ['status' => '400 (Bad Request)', 'message' => '', 'data' => ''];

        try {
            //$comment = $this->model_comment->getById($id);
            //$comment = $this->model_comment->getByColumn($id, 'issue_id');
            $comment = DB::table('comments')->where('issue_id',$id)->get();
            $result['data'] = $comment;
        } catch (Exception $ex) {
            $result['message'] = $ex->getMessage();
            return response($result, 400);
        }

        $result['status'] = '200 (Ok)';
        $result['message'] = 'Comments related to issue retrieved succesfully.';
        return response($result, 200);
    }

    public function get_all()
    {
        $result = ['status' => '400 (Bad Request)', 'message' => '', 'data' => ''];
        $result['data'] = $this->model_comment->get();
        $result['status'] = '200 (Ok)';
        $result['message'] = 'All Comments retrieved succesfully.';
        return response($result, 200);
    }

    public function create(CreateRequest $request)
    {
        $result = ['status' => '400 (Bad Request)', 'message' => '', 'data' => []];
        $header = $request->header('Authorization');
       
        $user = Auth::guard('api')->user();        
        try {
            $result['data'] = $this->model_comment->create(
                [
                    'user_id' => $user->id,
                    'content' => $request->content,
                    'issue_id' =>$request->issue_id              
                ]
            );
        } catch (Exception $ex) {
            $result['message'] = $ex->getMessage();
            return response($result, 400);
        }

        $result['status'] = '200 (Ok)';
        $result['message'] = 'Created Comment succesfully!';
        return response($result, 200);
    }

    public function delete($id)
    {
        $result = ['status' => '400 (Bad Request)', 'message' => '', 'data' => ''];

        try {
            $result['data'] = $this->model_comment->deleteById($id);
        } catch (Exception $ex) {
            $result['message'] = $ex->getMessage();
            return response($result, 400);
        }

        $result['status'] = '200 (Ok)';
        $result['message'] = 'Comment deleted succesfully';
        return response($result, 200);
    }
}
