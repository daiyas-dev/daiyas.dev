<?php

namespace App\Http\Controllers;

use App\Models\部署マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI04071Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 部署マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        // トランザクション開始
        DB::transaction(function() use ($params, $data) {
            $BushoCd = $params['部署CD'];

            DB::table('部署マスタ')->updateOrInsert(
                ['部署CD' => $BushoCd],
                $data
            );

            if (isset($params['ImageFile'])) {
                $src = public_path() . '\\images\\BushoStamp\\' . $params['ImageFile'];
                $dst = public_path() . '\\images\\BushoStamp\\' . $BushoCd . '.png';

                rename($src, $dst);
            }
        });

        $savedData = array_merge(['部署CD' => $params['部署CD']], $data);

        return response()->json([
            'result' => true,
            'model' => $savedData,
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $BushoCd = $request->BushoCd;

        // トランザクション開始
        DB::transaction(function() use ($BushoCd) {

            DB::table('部署マスタ')->where('部署CD', '=', $BushoCd)->delete();

        });

        return response()->json([
            'result' => true,
        ]);
    }

    /**
     * GetBushoListForDetail
     */
    public function GetBushoListForDetail($request)
    {
        $BushoCd = $request->BushoCd;
        $query = 部署マスタ::query()
            ->when(
                $BushoCd,
                function($q) use ($BushoCd){
                    return $q->where('部署CD', $BushoCd);
                }
            );

        $BushoList = $query->get();

        return response()->json($BushoList);
    }

    /**
     * UploadImage
     */
    public function UploadImage($request) {
        $BushoCd = $request->BushoCd;
        $FileName = $BushoCd . '_' . Carbon::now()->format('YmdHis') . '.png';

        $this->validate($request, [
            'file' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,png',
            ]
        ]);

        try {
            if ($request->file('file')->isValid([]))
            {
                $SavePath=public_path().'\\images\\BushoStamp\\'.$FileName;
                $spl = new \SplFileInfo($request->file);
                move_uploaded_file($spl->getPathname(), $SavePath);

                return response()->json([
                    'result' => true,
                    "file" => $FileName,
                ]);
            } else {
                return response()->json([
                    'result' => false,
                    "message" => '画像がアップロードされていないか不正なデータです。',
                ]);
            }
        } catch(Exception $ex) {
            return response()->json([
                'result' => false,
                "message" => '保存に失敗しました。',
            ]);
        }

    }
}
