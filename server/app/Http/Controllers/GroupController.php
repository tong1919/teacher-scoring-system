<?php

namespace App\Http\Controllers;

use App\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * GroupController constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        /**
         * 需要验证权限
         */
        $this->middleware('auth:api',[
            'only' => ['create','update','updateBatch','delete','deleteBatch']
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $query = Group::query()->withCount([
            'students as student_count',
            'students as complete_count' => function ($query) {
                $query->where('complete', 1);
            }
        ]);
        $query = $this->queryFilter($query);
        if ($this->req->get('getOptions') == 1) {
            return $this->getOptions($query);
        } else {
            return $this->paginateToJson($query);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        try {
            $students = $this->req->all();
            $create_count = count($students);
            $new_count = 0;
            foreach ($students as $student){
                // Todo: Validate
                $item = Group::query()->firstOrCreate($student);
                if ($item->wasRecentlyCreated){
                    $new_count++;
                }
            }
            return $this->json([
                'create_count' => $create_count,
                'new_count' => $new_count,
            ]);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $item = Group::query()->findOrFail($id);
        return $this->json($item);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function update($id)
    {
        $item = Group::query()->findOrFail($id);
        $validator = $this->ruleValidator($item->rules(),$item->ruleMessage());
        if ($validator){
            return $validator;
        }
        try {
            $input = $this->req->all();
            // Todo: Validate
            $item->update($input);
            return $this->json($item);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $item = Group::query()->findOrFail($id);
        try {
            $item->delete();
            return $this->json();
        } catch (\Exception $e) {
            return $this->error('Delete failed');
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateBatch()
    {
        if ($this->req->input('all') == 1){
            $data = $this->req->except('all');
            try {
                Group::query()->update($data);
                return $this->json();
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }
        }

        $ids = (array)$this->req->get('ids');
        $data = $this->req->except('ids');
        try {
            Group::query()->whereIn('id', $ids)->update($data);
            return $this->json();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBatch()
    {
        $ids = (array)$this->req->get('ids');
        try {
            Group::query()->whereIn('id', $ids)->delete();
            return $this->json();
        } catch (\Exception $e) {
            return $this->error('Delete failed');
        }
    }

}
