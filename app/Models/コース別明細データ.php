<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $日付
 * @property int $コースＣＤ
 * @property int $部署CD
 * @property int $配送担当者ＣＤ
 * @property float $持出し個数１
 * @property float $工場追加１
 * @property float $もらった１
 * @property float $やった１
 * @property float $見本１
 * @property float $残数１
 * @property float $持出し個数２
 * @property float $工場追加２
 * @property float $もらった２
 * @property float $やった２
 * @property float $見本２
 * @property float $残数２
 * @property float $持出し個数３
 * @property float $工場追加３
 * @property float $もらった３
 * @property float $やった３
 * @property float $見本３
 * @property float $残数３
 * @property float $持出し個数４
 * @property float $工場追加４
 * @property float $もらった４
 * @property float $やった４
 * @property float $見本４
 * @property float $残数４
 * @property float $持出し個数５
 * @property float $工場追加５
 * @property float $もらった５
 * @property float $やった５
 * @property float $見本５
 * @property float $残数５
 * @property float $持出し個数６
 * @property float $工場追加６
 * @property float $もらった６
 * @property float $やった６
 * @property float $見本６
 * @property float $残数６
 * @property float $持出し個数７
 * @property float $工場追加７
 * @property float $もらった７
 * @property float $やった７
 * @property float $見本７
 * @property float $残数７
 * @property float $チケット１持出枚数
 * @property float $チケット１持帰枚数
 * @property float $チケット１生協売
 * @property float $チケット１売枚数
 * @property float $チケット１売上金額
 * @property float $チケット２持出枚数
 * @property float $チケット２持帰枚数
 * @property float $チケット２生協売
 * @property float $チケット２売枚数
 * @property float $チケット２売上金額
 * @property float $チケット１現金集金
 * @property float $チケット２現金集金
 * @property float $ＶＢ集金３００個数
 * @property float $ＶＢ集金２００個数
 * @property float $金種１
 * @property float $金種２
 * @property float $金種３
 * @property float $金種４
 * @property float $金種５
 * @property float $金種６
 * @property float $金種７
 * @property float $金種８
 * @property float $金種９
 * @property float $金種１０
 * @property float $金種１１
 * @property float $小切手
 * @property float $領収書
 * @property float $合計
 * @property float $過剰金
 * @property float $セイブチケットＥお
 * @property float $セイブチケットＨお
 * @property float $セイブボーナス
 * @property float $ＢＶチケット３００
 * @property float $ＢＶチケット２００
 * @property float $予備金額１
 * @property float $予備金額２
 * @property int $予備ＣＤ１
 * @property int $予備ＣＤ２
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class コース別明細データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'コース別明細データ';

    /**
     * @var array
     */
    protected $fillable = ['配送担当者ＣＤ', '持出し個数１', '工場追加１', 'もらった１', 'やった１', '見本１', '残数１', '持出し個数２', '工場追加２', 'もらった２', 'やった２', '見本２', '残数２', '持出し個数３', '工場追加３', 'もらった３', 'やった３', '見本３', '残数３', '持出し個数４', '工場追加４', 'もらった４', 'やった４', '見本４', '残数４', '持出し個数５', '工場追加５', 'もらった５', 'やった５', '見本５', '残数５', '持出し個数６', '工場追加６', 'もらった６', 'やった６', '見本６', '残数６', '持出し個数７', '工場追加７', 'もらった７', 'やった７', '見本７', '残数７', 'チケット１持出枚数', 'チケット１持帰枚数', 'チケット１生協売', 'チケット１売枚数', 'チケット１売上金額', 'チケット２持出枚数', 'チケット２持帰枚数', 'チケット２生協売', 'チケット２売枚数', 'チケット２売上金額', 'チケット１現金集金', 'チケット２現金集金', 'ＶＢ集金３００個数', 'ＶＢ集金２００個数', '金種１', '金種２', '金種３', '金種４', '金種５', '金種６', '金種７', '金種８', '金種９', '金種１０', '金種１１', '小切手', '領収書', '合計', '過剰金', 'セイブチケットＥお', 'セイブチケットＨお', 'セイブボーナス', 'ＢＶチケット３００', 'ＢＶチケット２００', '予備金額１', '予備金額２', '予備ＣＤ１', '予備ＣＤ２', '修正担当者ＣＤ', '修正日'];

}
