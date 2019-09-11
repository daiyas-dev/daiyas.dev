/*!
 * UMCプロジェクト用 静的メソッド（便利関数の詰め合わせ）
 * Copyright 2019 Trustech.
 * [関連するプラグイン]
 * Jquery, veu.js, moment.js
 * Bootstrap DatePicker, Jquery TimePicker
 */

(function ($) {

    //■myUtilityオブジェクト定義
    /* *** このプラグインのメソッドは全て$.myUtilityのプロパティとして定義する *** */
    /* *** $.myUtility.（メソッド名）の形式で追加していく *** */
    $.myUtility = {};


    /* ****** ■ Method Start■ ******↓ */



    /* ****** evBindTelInputAdjust ******↓ */
    //@params parent_selecter : 基準セレクタオブジェクト（jquery）
    //@params vModel：viewModelオブジェクト（veu.js）

    //@author by H.Murata

    //■基準セレクタ内に 存在する data-is-tel=true を持つ全てのinput要素に対して
    //電話番号入力用のjquery, veu,js用の入力値調整のイベント処理を割り当てます

    //[使用方法]：
    //HTML側：　<input type="tel" id="EgTel" v-model="viewModel.EgTel" data-is-tel=true>
    //処理を割り当てたいinput要素に対して、data-is-tel=true　の属性を付与します
    //スクリプト側：　$.evBindTelInputAdjust($("#Page_SIP0202"), SIP0202.vue.viewModel);
    //initialize: function () の内部でメソッドをコールします。

    //上記のような指定を行うと…
    //$("#Page_SIP0202")のセレクタ内に含まれる [data-is-tel=true] を持つ全てのinput要素に対して、
    //キーボードから入力された文字列値のうち全角英数文字列を半角英数文字に変換し、値を再セットします。
    //また、引数vModelが指定されている為、input要素のID値でviewModelのプロパティを検索し値を更新します。

    //※　既に対象要素に何かしらのchangeイベントが割り当てている場合は使用ＮＧです
    //通常、parent_selecterには画面全体のformのIDで指定しますが、状況に応じて切り替えてください

    $.myUtility.evBindTelInputAdjust = function (parent_selecter, vModel) {

        //●基準セレクタ内に存在する input[data-is-tel=true] を取得
        var target = parent_selecter.find("input[data-is-tel=true]");
        if (target.length === 0) {
            //対象が存在しない
            return;
        }

        //var regex1 = /[Ａ-Ｚａ-ｚ０-９！＂＃＄％＆＇（）＊＋，－．／：；＜＝＞？＠［＼］＾＿｀｛｜｝]/g;
        var regex1 = /[０-９^－^]/g;
        var regex2 = /[^-^0-9^\.]/g;
        //●各々の要素に対して修正イベントをバインド
        target.each(function () {
            var that = $(this);

            //↓↓↓
            that.on("change", function () {

                if (this.value.match(regex1)) {
                    //アルファベット、数値を半角へ変換
                    var str = this.value.replace(regex1, function (s) { return String.fromCharCode(s.charCodeAt(0) - 0xFEE0) });
                } else {
                    var str = this.value;
                }

                //TELで使用する文字列のみ取得
                var result = str.replace(regex2, "");
                that.val(result);

                //viewModelが有効であれば
                if (vModel) {
                    vModel[this.id] = result;
                }


            });
            //↑↑↑

        });
    };
    /* ****** evBindTelInputAdjust ******↑ */



    /* ****** strDateFormat ******↓ */
    //@params str : 日付として認識可能な文字列
    //@params format：日付のフォーマットを指定する文字列（momentで使用可能なもの：未指定でYYYY/MM/DD）
    //@params default_value：フォーマット失敗時に返す値（未指定の場合は空文字）

    //@author by H.Murata

    //■文字列strをmomentを用いてformatで指定された日付文字列にフォーマットします
    //変換に失敗した場合や不正な日付が指定されていた場合はdefault_valueを返します
    $.myUtility.strDateFormat = function (str, format, default_value) {

        //default_valueに指定がない場合は空文字を初期値とする
        if (!default_value) {
            default_value = "";
        }

        //strが無効の場合
        if (!str) {
            return default_value;
        }

        //formatに指定がない場合
        if (!format) {
            format = "YYYY/MM/DD";
        }

        //momentを使用してstrが日付であるかパース後、指定フォーマットに変換したものを返す
        if (moment(str, "YYYY/MM/DD").isValid()) {
            //YYYY/MM/DD形式のmomentオブジェクトに変換
            var d = moment(str, "YYYY/MM/DD");

            if (moment(d, format).isValid) {
                //指定フォーマットで変換可能であれば変換結果を返す
                return d.format(format);
            }
        }

        //失敗時
        return default_value;

    };
    /* ****** strDateFormat ******↑ */



    /* ****** strTimeFormat ******↓ */
    //@params str : 時刻として認識可能な文字列
    //@params format：時刻のフォーマットを指定する文字列（momentで使用可能なもの：未指定でHH:mm）
    //@params default_value：フォーマット失敗時に返す値（未指定の場合は空文字）

    //@author by H.Murata

    //■文字列strをmomentを用いてformatで指定された時刻文字列にフォーマットします
    //変換に失敗した場合や不正な時刻が指定されていた場合はdefault_valueを返します
    $.myUtility.strTimeFormat = function (str, format, default_value) {

        //default_valueに指定がない場合は空文字を初期値とする
        if (!default_value) {
            default_value = "";
        }

        //strが無効の場合
        if (!str) {
            return default_value;
        }

        //formatに指定がない場合
        if (!format) {
            format = "HH:mm";
        }

        //momentを使用してstrが時刻であるかパース後、指定フォーマットに変換したものを返す
        if (moment(str, "HH:mm").isValid()) {
            //HH:mm形式のmomentオブジェクトに変換
            var t = moment(str, "HH:mm");

            if (moment(t, format).isValid) {
                //指定フォーマットで変換可能であれば変換結果を返す
                return t.format(format);
            }
        }

        //失敗時
        return default_value;

    };
    /* ****** strTimeFormat ******↑ */



    /* ****** evBindDatePickerInput ******↓ */
    //@params parent_selecter : 基準セレクタオブジェクト（jquery）
    //@params vModel：viewModelオブジェクト（veu.js）
    //@params extOption：datepicker生成時の追加Option（詳細はBootstrap DatepickerのDOCを参照）
    //language, format, autoclose, orientation については予め共通分を指定済の為、変更したいプロパティのみ指定して下さい
    //orientation は、画面によっては "bottom" で表示位置を固定される事をお勧めします（既定はauto）

    //@params format：Bootstrap DatePickerでの変更後にviewModelにセットする日付フォーマット（$.myUtility.strDateFormatを参照）

    //@author by H.Murata

    //■基準セレクタ内に 存在する data-is-date_picker=true を持つ全てのinput要素に対して
    //日付入力が使用可能な状態にします（date_pickerのみ）
    $.myUtility.evBindDatePickerInput = function (parent_selecter, vModel, extOption, format) {

        //●基準セレクタ内に存在する input[data-is-date_picker=true] を取得
        var target = parent_selecter.find("input[data-is-date_picker=true]");
        if (target.length === 0) {
            //対象が存在しない
            return;
        }

        //●date pickerのオプション（標準オプションから変更する場合はextOptionで指定）
        var option = {
            language: "ja",
            format: "yyyy/mm/dd",
            autoclose: true,
            orientation: "auto",
        };
        if (!extOption) {
            extOption = {};
        }
        //extOptionで上書き
        $.extend(true, option, extOption);

        //●各々の要素に対してdate pickerを有効にする
        target.each(function () {

            var that = $(this);
            //↓↓↓
            //●date pickerを有効にする
            that.datepicker(option).on("change", function () {
                //日付入力画面で変更後、viewModelの値を更新する
                if (vModel) {
                    if (format) {
                        //format指定時はviewModelにformat後の値を入れる
                        var formatted = $.myUtility.strDateFormat(this.value, format);
                        vModel[this.id] = formatted;
                    } else {
                        vModel[this.id] = this.value;
                    }
                }
            });

            //↑↑↑
        });
    };
    /* ****** evBindDatePickerInput ******↑ */



    /* ****** updateDatePickerInput ******↓ */
    //@params parent_selecter : 基準セレクタオブジェクト（jquery）
    //@params vModel：viewModelオブジェクト（veu.js）

    //@author by H.Murata

    //■viewModelとDatePickerの値の整合性を調整します
    //※画面表示データをaxisで再取得し、$forceUpdate()にて値の再セット後にこのメソッドを実行します
    //個々にDatePickerをセットするものに対して有効です。DateRangeを使用するものについてはupdateDateRangeInputを使ってください
    $.myUtility.updateDatePickerInput = function (parent_selecter, vModel) {

        //●基準セレクタ内に存在する input[data-is-date_picker=true] を取得
        var target = parent_selecter.find("input[data-is-date_picker=true]");
        if (target.length === 0) {
            //対象が存在しない
            return;
        }

        //●各々の要素に対してDatePickerとviewModelの値を合わせる
        target.each(function () {

            var that = $(this);
            //↓↓↓
            that.val(vModel[this.id]);
            that.datepicker("update");
            //↑↑↑
        });
    };
    /* ****** updateDatePickerInput ******↑ */



    /* ****** evBindDateRangeInput ******↓ */
    //@params parent_selecter : 基準セレクタオブジェクト（jquery）
    //@params vModel：viewModelオブジェクト（veu.js）
    //@params extOption：datepicker生成時の追加Option（詳細はBootstrap DatepickerのDOCを参照）
    //language, format, autoclose, orientation については予め共通分を指定済の為、変更したいプロパティのみ指定して下さい
    //orientation は、画面によっては "bottom" で表示位置を固定される事をお勧めします（既定はauto）

    //@params format：Bootstrap DatePickerでの変更後にviewModelにセットする日付フォーマット（$.myUtility.strDateFormatを参照）

    //@author by H.Murata

    //■基準セレクタ内に 存在する data-is-date_range=true を持つ全てのdiv要素に対して
    //日付入力が使用可能な状態にします（Date Rangeを適用するもの）
    $.myUtility.evBindDateRangeInput = function (parent_selecter, vModel, extOption, format) {

        //●基準セレクタ内に存在する div[data-is-date_range=true] を取得
        var target = parent_selecter.find("div[data-is-date_range=true]");
        if (target.length === 0) {
            //対象が存在しない
            return;
        }

        //●date pickerのオプション（標準オプションから変更する場合はextOptionで指定）
        var option = {
            language: "ja",
            format: "yyyy/mm/dd",
            autoclose: true,
            orientation: "auto",
        };
        if (!extOption) {
            extOption = {};
        }
        //extOptionで上書き
        $.extend(true, option, extOption);

        //●各々の要素に対してdate pickerを有効にする
        target.each(function () {

            var that = $(this);

            //↓↓↓
            //●date pickerを有効にする
            that.datepicker(option).on("change", function () {
                $("input", this).each(function (i, elem) {

                    //日付入力画面で変更後、viewModelの値を更新する
                    if (vModel) {
                        if (format) {
                            //format指定時はviewModelにformat後の値を入れる
                            var formatted = $.myUtility.strDateFormat(elem.value, format);
                            vModel[elem.id] = formatted;
                        } else {
                            vModel[elem.id] = elem.value;
                        }
                    }

                });
            });
            //↑↑↑
        });
    };
    /* ****** evBindDateRangeInput ******↑ */



    /* ****** updateDateRangeInput ******↓ */
    //@params parent_selecter : 基準セレクタオブジェクト（jquery）
    //@params vModel：viewModelオブジェクト（veu.js）

    //@author by H.Murata

    //■viewModelとDatePickerの値の整合性を調整します
    //※画面表示データをaxisで再取得し、$forceUpdate()にて値の再セット後にこのメソッドを実行します
    //DateRangeをセットするものに対して有効です。個々にDatePickerをセットするものについてはupdateDatePickerInputを使ってください
    $.myUtility.updateDateRangeInput = function (parent_selecter, vModel) {

        //●基準セレクタ内に存在する div[data-is-date_range=true] を取得
        var target = parent_selecter.find("div[data-is-date_range=true]");
        if (target.length === 0) {
            //対象が存在しない
            return;
        }

        //●datePickerの値初期化時と同時にvModelの値が書き換わる為、現在の値を退避
        var cloneModel = $.extend(true, {}, vModel);


        //●各々の要素に対してDatePickerとviewModelの値を合わせる
        target.each(function () {

            var that = $(this);

            //↓↓↓
            //DateRange内のInput要素各々に対して値を更新する
            var date_input = that.find("input");

            date_input.each(function (idx, el) {
                var that_input = $(this);

                //DatePickerの値を一度初期化
                that_input.datepicker("clearDates");

                //Input要素とvModelに元の値をセット
                that_input.val(cloneModel[this.id]);
                vModel[this.id] = cloneModel[this.id];

                //DatePickerを更新
                that_input.datepicker("update");
            });

            //↑↑↑
        });
    };
    /* ****** updateDateRangeInput ******↑ */



    /* ****** evBindTimePickerInput ******↓ */
    //@params parent_selecter : 基準セレクタオブジェクト（jquery）
    //@params vModel：viewModelオブジェクト（veu.js）
    //@params extOption：timepicker生成時の追加Option（詳細はJquery TimePickerのDOCを参照）

    //@params format：Bootstrap TimePickerでの変更後にviewModelにセットする時刻フォーマット（$.myUtility.strTimeFormatを参照）

    //@author by H.Murata

    //■基準セレクタ内に 存在する data-is-time_picker=true を持つ全てのinput要素に対して
    //時刻入力が使用可能な状態にします（time_pickerのみ）
    $.myUtility.evBindTimePickerInput = function (parent_selecter, vModel, extOption, format) {

        //●基準セレクタ内に存在する input[data-is-time_picker=true] を取得
        var target = parent_selecter.find("input[data-is-time_picker=true]");
        if (target.length === 0) {
            //対象が存在しない
            return;
        }

        //●time pickerのオプション（標準オプションから変更する場合はextOptionで指定）
        var option = {
            closeOnWindowScroll: true,
            step: 10,
            show2400: true,
            timeFormat: (time) => moment(time).format("HH:mm"),
        };
        if (!extOption) {
            extOption = {};
        }
        //extOptionで上書き
        $.extend(true, option, extOption);

        //●各々の要素に対してtime pickerを有効にする
        target.each(function () {

            var that = $(this);
            //↓↓↓
            //●time pickerを有効にする
            that.timepicker(option).on("change", function () {

                //日付入力画面で変更後、viewModelの値を更新する
                if (vModel) {
                    if (format) {
                        //format指定時はviewModelにformat後の値を入れる
                        var formatted = $.myUtility.strTimeFormat(this.value, format);
                        vModel[this.id] = formatted;
                    } else {
                        vModel[this.id] = this.value;
                    }
                }
            });
            //↑↑↑
        });
    };
    /* ****** evBindTimePickerInput ******↑ */



    /* ****** clearViewModel ******↓ */
    //@vModel : viewModel
    //@author by H.Murata

    //■引数で指定したviewModel内の各プロパティの値を空にします
    //最初のプロパティに対してのみ処理を行います
    //値が配列の場合は[]で初期化、オブジェクトの場合は{}で初期化、それ以外はnullで初期化します
    $.myUtility.clearViewModel = function (vModel) {

        //viewModelが無効であれば
        if (!vModel) {
            return false;
        }

        //返り値
        var ret = {};


        //各プロパティに対して処理
        $.each(vModel, function (idx, el) {
            switch ($.type(el)) {
                case "object":
                    ret[idx] = {};
                    break;
                case "array":
                    ret[idx] = [];
                    break;
                default:
                    ret[idx] = null;
                    break;
            }
        });

        //viewModelを更新
        $.extend(true, vModel, ret);

    };
    /* ****** clearViewModel ******↑ */



    /* ****** substrB ******↓ */
    //@params str : 文字列
    //@params len : バイト数


    //@author by E.Yamaguchi

    //■文字列strを、lenで指定したバイト数になるように切り出します。
    //※strのバイト数がlen以下の場合はそのまま返します。
    $.myUtility.substrB = function (text, len) {

        if (!text) {
            return text;
        }

        var text_array = text.split('');
        var count = 0;
        var str = '';

        for (i = 0; i < text_array.length; i++) {
            var n = escape(text_array[i]);
            if (n.length < 4) count++;
            else count += 2;
            if (count > len) {
                return str;
            }
            str += text.charAt(i);
        }

        return text;

    }
    /* ****** padLeftB ******↑ */




    /* ****** ■ Method End■ ******↑ */

})(jQuery);

