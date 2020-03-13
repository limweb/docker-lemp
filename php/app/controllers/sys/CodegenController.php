<?php

//----------------------------------------------
//FILE NAME:  CodegenController.php gen for Servit Framework Controller
//DATE: 2019-09-11(Wed)  19:43:34 

//----------------------------------------------
use	\Servit\Restsrv\RestServer\RestException;
use	\Servit\Restsrv\RestServer\RestController as BaseController;
use	Illuminate\Database\Capsule\Manager as Capsule;
use	Servit\Restsrv\Libs\Request; 
use	Servit\Restsrv\Libs\Linenotify;
use	Carbon\Carbon;
use \Servit\Restsrv\traits\DbTrait;

class CodegenController  extends BaseController {
    
    private $componemts = [
        "label",
        "text_span",
        "input_text_readonly",

        "toggle",
        "input_checkbox",
        "input_radio",
        "checkbox_group",
        "radio_group",
        "checkbox_group_relation",
        "radio_group_relation",

        "select",
        "select_multi",
        "select_relation",
        "select_multi_relation",

        "input_text",
        "input_number",
        "input_email",
        "input_image",
        "input_number",
        "input_password",
        "input_range",
        "input_tel",
        "input_url",
        "input_file",
        "input_upload",
        "input_uploads",

        "input_time",
        "input_date",
        "input_week",
        "input_month",
        "input_datetime",
        "datepicker",

        "textarea",
        "htmleditor",
        "json_editor",
        "maskdown_editor",

        "input_color",
        "colorpicker",
    ];

    private $numbertype = [
        "tinyint" => 'input_number', //	A very small integer
        "smallint" => 'input_number', //	A small integer
        "mediumint" => 'input_number', //	A medium-sized integer
        "int" => 'input_number', //	A standard integer
        "bigint" => 'input_number', //	A large integer
        "decimal" => 'input_number', //	A fixed-point number
        "float" => 'input_number', //	A single-precision floating point number
        "double" => 'input_number', //	A double-precision floating point number
        "bit" => 'input_number', //
    ];

    private $stringtype = [
        "char" => 'input_text', //	A fixed-length nonbinary (character) string
        "varchar" => 'input_text', //	A variable-length non-binary string
        "binary" => 'input_text', //	A fixed-length binary string
        "varbinary" => 'input_text', //	A variable-length binary string
        "tinyblob" => 'input_text', //	A very small BLOB (binary large object)
        "blob" => 'input_text', //	A small BLOB
        "mediumblob" => 'input_text', //	A medium-sized BLOB
        "longblob" => 'textarea', //	A large BLOB
        "tinytext" => 'input_text', //	A very small non-binary string
        "text" => 'textarea', //	A small non-binary string
        "mediumtext" => 'textarea', //	A medium-sized non-binary string
        "longtext" => 'textarea', //	A large non-binary string
        "enum" => 'select', //	An enumeration; each column value may be assigned one enumeration member
        "set" => 'select', //	A set; each column value may be assigned zero or more SET members
    ];

    private  $datetype = [
        "date"=>'input_date', //	A date value in CCYY-MM-DD format
        "time"=>'input_time', //	A time value in hh:mm:ss format
        "datetime"=>'input_datetime', //	A date and time value inCCYY-MM-DD hh:mm:ssformat
        "timestamp"=>'input_datetime', //	A timestamp value in CCYY-MM-DD hh:mm:ss format
        "year"=>'input_date', //	A year value in CCYY or YY format
    ];

    private  $spicialtype = [
        "geometry" => "input_text", //	A spatial value of any type
        "point" => "input_text", //	A point (a pair of X-Y coordinates)
        "linestring" => "input_text", //	A curve (one or more POINT values)
        "polygon" => "input_text", //	A polygon
        "geometrycollection" => "input_text", //	A collection of GEOMETRYvalues
        "multilinestring" => "input_text", //	A collection of LINESTRINGvalues
        "multipoint" => "input_text", //	A collection of POINTvalues
        "multipolygon" => "input_text", //	A collection of POLYGONvalues
    ];

    private  $booleantype = [
        "boolean" => "input_checkbox", //tineint(1)
    ];

    private function gentables() {
        $tables = [];
        $rawtables = Capsule::select('show tables');
        // $rawtables = Capsule::select('show tables where tables_in_lotnew = "test";');
        foreach ($rawtables as $rawtable) {
            foreach($rawtable as $table){
                $o = new stdClass();
                $o->table =$table;
                $o->pk = Capsule::select('SHOW KEYS FROM ' . $table . ' WHERE Key_name = "PRIMARY"');
                $o->fk = Capsule::select("SELECT concat(table_name,'.',column_name) AS 'fk',concat(referenced_table_name,'.',referenced_column_name) AS 'references' 
                            FROM information_schema.key_column_usage WHERE referenced_table_name IS NOT NULL 
                            AND table_schema='dbname' AND table_name='$table'");
                $o->modelclass= $this->camelize(strtolower($this->plural_to_singular($table)));
                $o->isTable = true;
                $o->isModelclass = true;
                $o->isServiceclass = true;
                $o->isCtrlClass = true;
                $o->isVueui = false;
                $o->isVue2Typem = true;
                $o->isVue3 = false;
                $o->isSvelte = false;
                $o->relations = [];
                $o->isTimestamps = false;
                $o->serviceclass = $o->modelclass.'Service';
                $o->controllerclass = $o->modelclass.'Controller';
                $o->baseRoute = '/api/' . strtolower($o->table);
                $cols = Capsule::select('DESCRIBE '.$o->table);

                $o->isSoftdelete = false;
                $o->softdelete = '';
                $o->createdat = '';
                $o->updatedat = '';

                $iscreated = false;
                $isupdated = false;
                foreach($cols as $col){
                    if($col->Field == 'deleted_at'){
                        $o->isSoftdelete = true;
                        $o->softdelete = 'deleted_at';
                    }
                    if($col->Field == 'created_at'){
                        $iscreated = true;
                        $o->createdat = 'created_at';
                    }

                    if($col->Field == 'updated_at'){
                        $isupdated = true;
                        $o->updatedat = 'updated_at';
                    }
                }
                if($iscreated && $isupdated){
                    $o->isTimestamps = true;
                }

                $o->cols = $this->gencols($cols);
                $o->allCols = true;
                $o->allShow	 = true;
                $o->allEdit	 = true;
                $o->allCreate = true;
                $o->allDelete = true;
                $tables[] = $o;
            }
        }
        $json = json_decode(json_encode($tables));
        // dump($json[0]);
        $o = new stdClass();
        $o->components = $this->componemts;
        $o->tables = $tables;
        return $o;
        // dump($o);
        // dump($o->tables[0]->cols);
    }

    private function gencols($table){
        $cols =[];
        foreach($table as $key => $col ){
            $o = new stdClass();
            $o->rawcol = $col;
            $o->field = $col->Field;
            $o->isCol = true;
            $o->label = ucfirst($this->camelize(strtolower($col->Field))); // Label of Field
            $o->requed = $col->Null=='NO'?:'false';
            $o->tablea = '';
            $o->tableb = '';
            $o->tablea_col = '';
            $o->tableb_col = '';
            $o->ralation='';
            $o->ralation_name = '';
            //key-----    
            $o->pkey = $col->Key =='PRI'?:false;
            $o->default = $col->Default;
            $o->extra = $col->Extra;

            //CRUD
            $o->isGuarded  = false;
            $o->isShow = true;   //R View
            $o->isCreate = true; //C Add or Create
            $o->isDelete =true; //D Delete
            $o->isEdit = true; // Edit
            $o->isSearch = false; // Edit
            $o->isSort = false;
            $o->sort = 'asc';

            if($o->pkey && $o->extra == "auto_increment"){
                $o->isGuarded  = true;
                $o->isCreate = false; //C Add or Create
            } 
            if($o->field =='created_at'){
                $o->isCreate = false; //C Add or Create
                $o->isEdit = false; // Edit
                $o->isDelete =false; //D Delete
                $o->isShow = false;   //R View
            }
            if($o->field =='updated_at'){
                $o->isCreate = false; //C Add or Create
                $o->isEdit = false; // Edit
                $o->isDelete =false; //D Delete
                $o->isShow = false;   //R View
            }
            if($o->field == 'deleted_at'){
                $o->isCreate = false; //C Add or Create
                $o->isEdit = false; // Edit
                $o->isDelete =false; //D Delete
                $o->isShow = false;   //R View
            }
            if($o->field == 'password'){
                $o->isCreate = false; //C Add or Create
                $o->isEdit = false; // Edit
                $o->isDelete =false; //D Delete
                $o->isShow = false;   //R View
            }

            $o->type = $this->gentype($col->Type);
            $cols[] = $o;
        }
        return $cols;
    }

    private function gentype($type) {
        $lists = explode(' ',$type);
        $o = new stdClass();
        $o->rawtype = $type;
        $type = isset($lists[0])?$lists[0]:'';
        if($type) {
            preg_match('/(?P<type>\w+)($|\((?P<length>(\d+|(.*)))\))/', $type, $field);
            $o->type = isset($field['type']) ? $field['type'] : null;
            $o->length = isset($field['length']) ? explode(',',$field['length']) : [];
        } 

        if($o->type){
            $stype = strtolower($o->type);

            if (array_key_exists($stype, $this->numbertype ) ) {
                switch ($stype) {
                    case 'tinyint':
                            if(isset($o->length[0]) && $o->length[0] == 1) {
                                $o->component = 'input_checkbox';
                            } else {
                                $o->component = $this->numbertype[$stype];
                            }
                        break;
                    default:
                            $o->component = $this->numbertype[$stype];
                        break;
                }
            } else if(array_key_exists($stype,$this->spicialtype)) {
                $o->component = $this->spicialtype[$stype];
            } else if(array_key_exists($stype,$this->booleantype)) {
                $o->component = $this->booleantype[$stype];
            } else if(array_key_exists($stype,$this->datetype)) {
                $o->component = $this->datetype[$stype];
            } else if(array_key_exists($stype,$this->stringtype)) {
                switch ($stype) {
                    case 'enum':
                    case 'set':
                        $o->choices = $o->length;
                        $o->length = [];
                        $o->component = $this->stringtype[$stype];
                        break;
                    default:
                        if( count($o->length) == 0 || ((isset($o->length[0]) && $o->length[0] == 0 )|| $o->length[0] > 255) ){
                            $o->component = 'textarea';
                        } else {
                            $o->component = 'input_text';
                        }
                        break;
                }

            } else {
                $o->component = 'input_text';
            }
        } else {
            $o->component = 'input_text';
        }
        return $o;
    }

    private  function camelize($input, $separator = '_'){
        return str_replace($separator, '', ucwords($input, $separator));
    }

    private function is_allcaps($string){
        $last_letter = mb_substr($string, -1, 1, 'UTF-8');
        return $last_letter === mb_strtoupper($last_letter, 'UTF-8');
        // otherwise use cytpe_upper() and setlocale()
    }

    private function plural_to_singular($string){
        // quick return of "untouchables"
        if(preg_match('~^(?:[oó]culos|parab[eé]ns|f[eé]rias)$~iu', $string))
        {
            return $string;
        }

        $regex_map = [
            '~[õã]es$~iu' => 'ão',
            '~(?:[áó].*e|[eé])is$~iu' => 'el',
            '~[^eé]\Kis$~iu' => 'l',
            '~ns$~iu' => 'm',
            '~eses$~iu' => 'ês',
            '~(?:[rzs]\Ke)?s$~iu' => ''
        ];

        foreach ($regex_map as $pattern => $replacement)
        {
            $singular = preg_replace($pattern, $replacement, $string, 1, $count);
            if ($count)
            {
                // return $this->is_allcaps($string) ? ucfirst(mb_strtolower($singular)) : ucfirst($singular);
                return $singular;
            }
        }
        return $string;
    }    
    
    /**
    *@noAuth
    *@url GET /index
    *----------------------------------------------
    *FILE NAME:  CodegenController.php gen for Servit Framework Controller
    *DATE:  2019-09-11(Wed)  19:43:48 
    
    *----------------------------------------------
    */
    public function index(){
        $dbs = $this->gentables();
        $dbsjson = json_encode($dbs);
        $dbname = $this->camelize($this->server->config->dbconfig['database']);        
        $html = <<<HTML
        <!DOCTYPE html>   
        <html lang="en">   
        <head>   
            <title>VUEVM CODEGEN BY TLEN!</title>   
            <meta charset="utf-8">  
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
            <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        HTML;        
        $html .='<style>
                #myBtn {

                    display: none; /* Hidden by default */
                    position: fixed; /* Fixed/sticky position */
                    bottom: 20px; /* Place the button at the bottom of the page */
                    right: 30px; /* Place the button 30px from the right */
                    z-index: 99; /* Make sure it does not overlap */
                    border: none; /* Remove borders */
                    outline: none; /* Remove outline */
                    background-color: red; /* Set a background color */
                    color: white; /* Text color */
                    cursor: pointer; /* Add a mouse pointer on hover */
                    padding: 15px; /* Some padding */
                    border-radius: 10px; /* Rounded corners */
                    font-size: 18px; /* Increase font size */
                }

                #myBtn:hover {
                    background-color: #555; /* Add a dark-grey background on hover */
                }
            </style>';
            $html .= <<<HTML
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>  
            <script src="https://unpkg.com/vue/dist/vue.min.js"></script>
            <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
            <script src="https://unpkg.com/vue-ls@3.2.1/dist/vue-ls.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.2.2/jszip.min.js" integrity="sha256-gy5W5/rXWluWXFRvMWFFMVhocfpBe7Tf4SW2WMfjs4E=" crossorigin="anonymous"></script>
            <script>
                var dbs = $dbsjson;
            </script>
        <head> 
        <body> 
            <div id="app" class="pl-4">
            <center><h3>AUTO GEN API/VUEUI CRUD FROM DATABASE</h3></center>
            <div class="d-flex justify-content-between" style="cursor: pointer;">
                <div @click="togglealltable"><b>Database:</b><input type="checkbox" v-model="alltable" /> $dbname
                </div>
                <div>&nbsp;</div>
                <div>&nbsp;</div>
                <div>&nbsp;</div>
                <div>&nbsp;</div>
                <div>&nbsp;</div>
                <div>&nbsp;</div>
                <div class="text-nowrap  pl-2"><input type="checkbox" v-model="isDownload" />D/L ZIP</div>
                <div class="text-nowrap  pl-2"><input type="checkbox" v-model="isGenbckend" />GEN Backend</div>
                <div @click="toggleVue2typemodule"><input type="checkbox" v-model="isVue2typemodule" >Vue2-typeModule</div>
                <div @click="toggleVue2"><input type="checkbox" v-model="isVue2" >Vue2</div>
                <div @click="toggleVue3"><input type="checkbox" v-model="isVue3" >Vue3</div>
                <div @click="toggleSvelte"><input type="checkbox" v-model="isSvelte" >Svelte</div>
                <div @click="togglesoftdelete"><input type="checkbox" v-model="allsoftdel" >Softdelete</div>
                <div @click="toggletimestamp"><input type="checkbox" v-model="alltimestamp" >Timestamps</div>
                <button @click="genall" class="btn btn-sm btn-success" style="float:right" >GENCODE FOR ALL TABLES</button>
            </div>
            <div id="accordion">
        HTML;
                // dump($dbs->tables[0]->cols);
                echo $html;
                // dump($dbs);
        ?>
            <div class="card ">
                <div class="card-header d-flex align-items-start  justify-content-between" style="background-color:#c3c3c3">
                    <label>เลือกตาราง
                        <select v-model="loginsys.table" @change="{loginsys.username=''; loginsys.password=''}">
                            <option value="">กรุณาเลือกตารางสำหรับ Login</option>
                            <option v-for="(table,idt) in tables" :key="idt" :value="table.table">{{table.table}}</option>
                        </select>
                    </label>
                    <label>เลือก Field User/Email
                        <select v-model="loginsys.username">
                            <option value="">กรุณาเลือก Field username</option>
                            <option v-for="(fuser,idx) in getcols(loginsys.table)" :value="fuser.field" :key="idx">{{fuser.field}}</option>
                        </select>
                    </label>
                    <label>เลือก Field Password
                        <select v-model="loginsys.password">
                            <option value="">กรุณาเลือก Field password</option>
                            <option v-for="(fuser,idx) in getcols(loginsys.table)" :value="fuser.field" :key="idx">{{fuser.field}}</option>
                        </select>
                    </label>
                    <label>เลือก ชนิดตรวจสอบการ Login
                        <select v-model="loginsys.logintype">
                            <option value="JWT">กรุณาเลือก JWT </option>
                        </select>
                    </label>
                    <div class="d-flex flex-column">
                    <label>ISS 
                        <select v-model="loginsys.iss">
                            <option value="">กรุณาเลือก ISS </option>
                            <option v-for="(fuser,idx) in getcols(loginsys.table)" :value="fuser.field" :key="idx">{{fuser.field}}</option>
                        </select>
                    </label>
                    <label>AUD 
                        <select v-model="loginsys.aud">
                            <option value="">กรุณาเลือก AUD </option>
                            <option v-for="(fuser,idx) in getcols(loginsys.table)" :value="fuser.field" :key="idx">{{fuser.field}}</option>
                        </select>
                    </label>
                    <label>EXP
                        <select v-model="loginsys.exp">
                            <option value="0"> ตลอดชีพ</option>
                            <option value="1">1 นาที</option>
                            <option value="2">5 นาที</option>
                            <option value="10">10 นาที</option>
                            <option value="15">15 นาที</option>
                            <option value="30">30 นาที</option>
                            <option value="60">1 ชั่วโมง</option>
                            <option value="720">ครึ่งวัน</option>
                            <option value="1440">1 วัน</option>
                            <option value="10080">7 วัน</option>
                        </select>
                    </label>
                    <label>Name
                        <select v-model="loginsys.name">
                            <option value="">---เลือก Name---</option>
                            <option v-for="(fuser,idx) in getcols(loginsys.table)" :value="fuser.field" :key="idx">{{fuser.field}}</option>
                        </select>
                    </label>


                    </div>
                    <div class="text-nowrap  pl-2"><a href="#codegen"><input @click="genlogin" type="button" class="btn btn-sm btn-primary" value="GEN login SYSTEM" /></a></div>
                </div>
            </div>
            <div v-for="(table,idx) in tables" class="card ">
                <div class="card-header d-flex align-items-start  justify-content-between">
                    <div>
                        <div class="d-flex flex-row">
                            <div>
                                <label class="pr-2 "> #{{idx+1}}</label>
                            </div>
                            <div>
                                <input type="checkbox" v-model="table.isTable" />
                                <a class="{{table}}card-link" data-toggle="collapse" :href="'#table'+idx">
                                    {{table.table}}
                                </a>
                            </div>
                            <div class="text-nowrap  pl-2"><input type="checkbox" v-model="table.isModelclass" />
                                <input placeholder="Model Class" v-model="table.modelclass" />
                            </div>
                            <div class="text-nowrap  pl-2"><input type="checkbox" v-model="table.isServiceclass" />
                                <input placeholder="Service Class" v-model="table.serviceclass" />
                            </div>
                            <div class="text-nowrap  pl-2 d-flex flex-row">
                                <div class="d-flex">
                                    <input type="checkbox" v-model="table.isCtrlClass" />
                                    <input placeholder="Controller Class" v-model="table.controllerclass" />
                                </div>
                                <div>
                                    &nbsp;&nbsp;&nbsp;<input placeholder="base Route" v-model="table.baseRoute" />
                                </div>
                            </div>
                            <div class="text-nowrap  pl-2"><input type="checkbox" v-model="table.isVue2Typem" />* Vue2 TM</div>
                            <div class="text-nowrap  pl-2"><input type="checkbox" v-model="table.isVueui" />* Vue2 UI</div>
                            <div class="text-nowrap  pl-2"><input type="checkbox" v-model="table.isVue3" />* Vue3 UI</div>
                            <div class="text-nowrap  pl-2"><input type="checkbox" v-model="table.isSvelte" />* SVELTE UI</div>
                        </div>
                        <div class="d-flex flex-row ">
                            <div style="height:80px">
                                    <button @click="addrelation(table)"><i class="fa fa-plus" aria-hidden="true"></i></button>
                            </div>
                            <div class="d-flex flex-column">
                                <div class="d-flex flex-nowrap" v-for="(related,rdx) in table.relations" style="background-color: aquamarine;">
                                    <label>#{{rdx+1}}</lable>
                                    <input type="text" v-model="related.name" placeholder="functionname" /> 
                                    <input type="text" v-model="related.bname" placeholder="functionname" /> 
                                    <select v-model="related.type" @change="selectrelatetb(related)">
                                        <option value="0">--เลือก-relation--</option>
                                        <option value="1">HasOne</option>
                                        <option value="2">HasMany</option>
                                    </select>
                                    <select v-model="related.relatetable_name" @change="selectrelatetb(related)">
                                        <option value="">--เลือก-ตาราง relation--</option>
                                        <option  v-for="(selecttable,idxx) in tables" :value="selecttable.table">{{selecttable.table}}</option>
                                    </select>
                                    <select v-model="related.field_master" >
                                        <option value="">--เลือก-field ตาราง {{table.table?table.table:'ต้นทาง'}}--</option>
                                        <option  v-for="(ffield,idxy) in table.cols" :value="ffield.field">{{ffield.field}}</option>
                                    </select>
                                    <select v-model="related.field_relate">
                                        <option value="">--เลือก-field ตาราง {{related.relatetable_name?related.relatetable_name:'ปลายทาง'}}--</option>
                                        <option  v-for="(lfield,idxz) in getcolsbytable(related.relatetable_name)" :value="lfield.field">{{lfield.field}}</option>
                                    </select>
                                    <label><input type="checkbox" v-model="related.isWith" />$with</label>
                                    <input type="text" v-model="related.comment" placeholder="commment" />
                                    <button @click="delrelation(rdx,table.relations)" ><i class="fa fa-trash" aria-hidden="true"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-nowrap  pl-2"><input type="checkbox" v-model="table.isSoftdelete" />* Soft Delete <br />
                        <div>
                            <select v-model="table.softdelete">
                                <option value="">--deleted at--</option>
                                <option v-for="(dt,idd) in datetimefields(table.cols)" :key="idd">{{dt.field}}</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-nowrap  pl-2"><input type="checkbox" v-model="table.isTimestamps" />* Timestamps
                        <div>
                            <select v-model="table.createdat">
                                <option value="">--created_at--</option>
                                <option v-for="(dt,idc) in datetimefields(table.cols)" :key="idc">{{dt.field}}</option>
                            </select><br />
                            <select v-model="table.updatedat">
                                <option value="">--updated_at--</option>
                                <option v-for="(dt,idu) in datetimefields(table.cols)" :key="idu">{{dt.field}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-nowrap  pl-2"><a href="#codegen"><input @click="genbytable(table)" type="button" class="btn btn-sm btn-primary" value="GEN by Table ZIP" /></a></div>
                 </div>

                <div :id="'table'+idx" class="collapse" data-parent="#accordion">
                    <div class="card-body ">
                        <table class="table table-striped table-bordered ">
                            <thead>
                                <tr>
                                    <th @click="togglecol(table)">
                                    <input type="checkbox" v-model="table.allCols" />Column Name</th>
                                    <th style="width:150px;" @click="toggledelete(table)">Is Sort</th>
                                    <th style="width:150px;" @click="toggledelete(table)">Is Search</th>
                                    <th style="width:150px;" @click="toggleshow(table)"><input type="checkbox"   v-model="table.allShow"  /> Is Show</th>
                                    <th style="width:150px;" @click="toggleedit(table)"><input type="checkbox"   v-model="table.allEdit"  /> Is Edit</th>
                                    <th style="width:150px;" @click="togglecreate(table)"><input type="checkbox" v-model="table.allCreate"  /> Is Create</th>
                                    <th style="width:150px;" @click="toggledelete(table)"><input type="checkbox" v-model="table.allDelete"  /> Is Delete</th>
                                    <th>* Form Componet</th>
                                    <th>Ralation Config</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(col,idk) in table.cols">
                                    <td class="d-flex align-items-center">
                                        #{{idk+1}}&nbsp;
                                        <input type="checkbox" v-model="col.isCol" />
                                        &nbsp;&nbsp;
                                        <input type="text" :placeholder="'label ' + col.field " v-model="col.label" /></td>
                                    <td>
                                        <div class="d-flex flex-row">
                                            <input type="checkbox" v-model="col.isSort" />
                                            <select v-model="col.sort">
                                                <option value="asc">Asc</option>
                                                <option value="desc">Desc</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td><input type="checkbox" v-model="col.isSearch" /></td>
                                    <td><input type="checkbox" v-model="col.isShow" /></td>
                                    <td><input type="checkbox" v-model="col.isEdit" /></td>
                                    <td><input type="checkbox" v-model="col.isCreate" /></td>
                                    <td><input type="checkbox" v-model="col.isDelete" /></td>
                                    <td>
                                        <select name="components" v-model="col.type.component">
                                            <option v-for="(component,idy) in components" v-model="component" :key="idy">
                                                {{component}}</option>';
                                        </select>
                                    </td>
                                    <td v-if="chkrelation(col.type.component)">
                                        <div>
                                            <select name="relation" v-model="col.ralation">
                                                <option value="0">--เลือก-relation--</option>
                                                <option value="1">One To One</option>
                                                <option value="2">One To Many</option>
                                            </select>
                                            <input type="text" v-model="col.relation_name" plactholder="ralation name" />
                                            <div>
                                                <div>
                                                    <select name="tablea" v-model="col.tablea">
                                                        <option>--เลือก-table--</option>
                                                        <option v-for="(table,idt) in tables" :key="idt" :value="table.table">
                                                            {{table.table}}</option>
                                                    </select>
                                                    <select name="fielda" v-model="col.tablea_col">
                                                        <option>--เลือก-field--</option>
                                                        <option v-for="(column,idc) in getcols(col.tablea)" :key="idc">
                                                            {{column.label}}</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <select name="tableb" v-model="col.tableb">
                                                        <option>--เลือก-table--</option>
                                                        <option v-for="(table,idtt) in tables" :key="idtt" :value="table.table">
                                                            {{table.table}}</option>
                                                    </select>
                                                    <select name="fieldb" v-model="col.tableb_col">
                                                        <option>--เลือก-field--</option>
                                                        <option v-for="(columnc,idcc) in getcols(col.tableb)" :key="idcc">
                                                            {{columnc.label}}</option>
                                                    </select>
                                                </div>
                                                <div id="xtable'.$i.'">
                                                    <a class="card-link" data-toggle="collapse" href="#field'.$i.'">--เลือก-field ที่จะแสดง--</a>
                                                    <br />
                                                    <ul id="field'.$i.'" class="collapse" data-parent="#xtable'.$i.'">
                                                        <!-- $showfields = [];
                                                    foreach($dbs->tables[0]->cols as $fields){
                                                        $showfields[] = $fields->field;
                                                        echo '<li><input type="checkbox">',$fields->field,'</li>';
                                                    } -->
                                                    </ul>
                                                    <!-- '[',join(',',$showfields),']'; -->
                                                </div>
                                            </div>
                                            <div>
                                    </td>
                                    <td v-else>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- End OF Accordion -->
        <div class="pt-4">
            <button  style="float:right" @click="genall" class="btn btn-sm btn-success">GENCODE FOR ALL TABLES</button>
            <br /><br />
        </div>
        <div>
            <h5>comments * </h5>
            <p class="pl-4"><b>softdelete</b>: จะต้องเลือก field 1 รายการที่เป็น ชนิด datatime ไม่ซ้ำกับ timestamps<br />
                <b>timestamps</b>: จะต้องเลือก filed ที่เป็น ชนิด datatime จำนวน 2 รายการ ตามตัวอย่าง ถ้าไม่มีให้ไปสร้างใน db ก่อน<br />
                <b>vueui</b>: จะทำการ gen CRUD ตาม รายการ ทีเลือกให้<br />
                <b>component</b>: default จะเลือก component ตาม datatype ที่มากับ database นั้น ๆ ถ้าต้องการเปลีย่นสามารถเลือกเปลี่ยนได้ตามความเหมาะสมกับการใช้งาน <br/>
                <b>is Search</b>: สำหรับ columns ที่มีไว้ ค้นห้าด้วย keyword ทั้งฝั่ง backend และ frontend ค่า default จะไม่กำหนดให้ ถ้าต้องการกรุณากำหนดเอง </br>
                <b>Is Show</b>:	Column ที่จะแสดงในส่วนของ Info และ Data Tables <br/>
                <b>Is Edit</b>:	Column ที่เปิดให้ แก้ไขได้  <br/>
                <b>Is Create</b>:	Column ที่จะให้ fillable สำหรับ   <br/>
                <b>Is Delete</b>:  Cloumn ที่จะแสดงข้อมูลก่อน delete <br/>
            </p>
        </div>
        <br /><br /><br /><br /><br />
        <pre id="codegen" style="border: 1px;width: 100%;min-height: 100px;background-color: #eeeeee;padding: 10px;" >{{codegen}}</pre>
        <button id="myBtn" style="float: right;margin-right: 20px;" @click="toTop">TOP</button>
        </div><br /><br />
        <script>
        options = {
            namespace: 'vuejs__', // key prefix
            name: 'ls', // name variable Vue.[ls] or this.[$ls],
            storage: 'local', // storage name session, local, memory
        };
        Vue.use(VueStorage, options);
        window.vm = new Vue({
            mixins: [],
            data() {
              return {
                codegen: 'CODE GEN AREA',
                tables: dbs.tables,
                components: dbs.components,
                alltable: true,
                allsoftdel: false,
                alltimestamp: false,
                isVue2typemodule: true,
                isVue2: false,
                isVue3: false,
                isSvelte: false,
                codedata:[],
                codelogindata:{},
                isDownload:false,
                isGenbckend:false,
                relationtables:{},
                loginsys:{
                    logintype:'JWT',
                    username:'',
                    password:'',
                    table:'',
                    name:'',
                    iss:'',
                    aud:'',
                    exp:0
                }
              }
            },
            el: "#app",
            methods: {
                datetimefields(cols) {
                    return cols.filter(c => {
                        return (c.type.type == 'datetime' || c.field.toLocaleLowerCase().indexOf('date') != -1);
                    })
                },
                getcols(table) {
                    if (table) {
                        tablex = this.tables.find(t => t.table == table)
                        if (!!tablex) {
                            return tablex.cols
                        } else {
                            return [];
                        }
                    } else {
                        return [];
                    }
                },
                chkrelation(component) {
                    let arr = component.split('_');
                    if (arr[arr.length - 1] == 'relation') {
                        return true;
                    } else {
                        return false;
                    }
                },
                togglecol(table){
                    table.allCols = !table.allCols;
                    table.cols.map(col=>col.isCol=table.allCols);
                    console.log('table--->',table.cols);
                },
                toggleVue2typemodule(){
                    this.isVue2typemodule=!this.isVue2typemodule;
                    this.tables.map(table=>table.isVue2Typem=this.isVue2typemodule);
                },
                toggleVue2(){
                    this.isVue2=!this.isVue2;
                    this.tables.map(table=>table.isVueui=this.isVue2);
                },
                toggleVue3(){
                    this.isVue3=!this.isVue3;
                    this.tables.map(table=>table.isVue3 = this.isVue3);
                },
                toggleSvelte(){
                    this.isSvelte=!this.isSvelte;
                    this.tables.map(table=>table.isSvelte =this.isSvelte);
                },
                togglesoftdelete(){
                    this.allsoftdel = !this.allsoftdel;
                    this.tables.map(table=>table.isSoftdelete = this.allsoftdel);
                },
                toggletimestamp(){
                    this.alltimestamp = !this.alltimestamp;
                    this.tables.map(table=>table.isTimestamps = this.alltimestamp);
                },
                toggleshow(table){
                    table.allShow = !table.allShow;
                    table.cols.map(col=>col.isShow = table.allShow);    
                },
                toggleedit(table){
                    table.allEdit = !table.allEdit;
                    table.cols.map(col=>col.isEdit = table.allEdit);    
                },
                toggledelete(table){
                    table.allDelete = !table.allDelete;
                    table.cols.map(col=>col.isDelete = table.allDelete);    
                },
                togglecreate(table){
                    table.allCreate = !table.allCreate;
                    table.cols.map(col=>col.isCreate = table.allCreate);    
                },
                togglealltable(){
                      this.alltable = !this.alltable;
                      this.tables.map(table=>table.isTable = this.alltable);
                },
                toTop(){
                    document.body.scrollTop = 0; // For Safari
                    document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
                },
                addrelation(table){
                    console.log('--table---',table);
                    let pks = this.getpkname(table.pk);
                    console.log('pks-->',pks);
                    let relation = {
                        type: 0,
                        comment:'',
                        isWith:false,
                        
                        name: '',
                        relatetable_name:'',
                        relatetable_class:'',
                        field_relate:'',

                        bname:table.table,
                        master_table:table.table,
                        master_class: table.modelclass,
                        field_master: (pks.length==1?pks[0]:''),
                        
                    }
                    this.relationtables[table.table].push(relation);
                    table.relations = this.relationtables[table.table];
                },
                saverelation(){
                    this.$ls.set('relations',this.relationtables);
                    this.$ls.set('loginsys',this.loginsys);
                },
                delrelation(rdx,relations){
                    if(confirm('ต้องการลบหรือไม่ง')){
                        relations.splice(rdx,1);
                    }
                },
                getcolsbytable(tablename){
                    if(tablename){
                        let table = this.tables.find(table=>table.table == tablename);
                        return table.cols;
                    } else {
                        return [];
                    }
                },
                genAllHtmltable(tables){
                   this.codegen = '';
                   this.codedata = [];
                    let logincode = this.genlogincode();
                    this.codelogindata = logincode;
                    Object.keys(logincode).map(key=>{
                        this.codegen += logincode[key];
                    })
                   let routecodegen  = "//---------------------------------------------------routebygen.php \n";
                   tables.map(table=>{
                       let tabledata = this.genHtmltable(table);
                       this.codegen  += tabledata.model.doc;
                       this.codegen  += tabledata.service.doc;
                       this.codegen  += tabledata.controller.doc;

                       this.codegen  += tabledata.vuetm.doc;
                       this.codegen  += tabledata.vue2.doc;
                       this.codegen  += tabledata.vue3.doc;
                       this.codegen  += tabledata.svelte.doc;

                       this.codegen  += tabledata.sql.doc;
                       routecodegen  += tabledata.routebygen.doc;
                       this.codedata.push(tabledata);
                   });
                   //------------add logincodedata------------- 

                    if(this.codelogindata.model){
                        this.codegen += "//---------------------------------Login.php \n";
                        this.codegen += this.codelogindata.model;
                    }
                    if(this.codelogindata.jwtsrv){
                        this.codegen += "//---------------------------------JwtService.php \n";
                        this.codegen += this.codelogindata.jwtsrv;
                    }
                    if(this.codelogindata.loginsrv){
                        this.codegen += "//---------------------------------LoginService.php \n";
                        this.codegen += this.codelogindata.loginsrv;
                    }
                    if(this.codelogindata.loginctl){
                        this.codegen += "//---------------------------------LoginController.php \n";
                        this.codegen += this.codelogindata.loginctl;
                        routecodegen  = '$server->addClass("LoginController","",""); \n' + routecodegen;
                    }
                    if(this.codelogindata.jwtctl){
                        this.codegen += "//---------------------------------JwtController.php \n";
                        this.codegen += this.codelogindata.jwtctl;
                        routecodegen  = '$server->addClass("JwtController","",""); \n' + routecodegen;
                    }
                    if(this.codelogindata.vuehtml){
                        this.codegen += "//---------------------------------Login.vue \n";
                        this.codegen += this.codelogindata.vuehtml;
                    }

                   this.codegen += routecodegen;
                   this.codegen += '\n\n';
                },
                gencoderelation(){
                        let coderelation = {
                            errot:false,
                            err:''
                        };
                        Object.keys(this.relationtables).map(table=>{
                            if( this.relationtables[table].length > 0 ) {
                                if(!coderelation[table]){
                                    coderelation[table]={
                                        xwith:[],
                                        func:{},
                                    }
                                }
                                this.relationtables[table].map(rtb=>{
                                    let xwith = '';
                                    let rfunc = '';
                                    let bfunc = '';
                                    if( rtb.name && rtb.bname && rtb.master_class && rtb.relatetable_class && rtb.field_master && rtb.field_relate ){
                                        if(rtb.isWith){
                                            xwith += '"'+ rtb.name+'",';
                                            coderelation[table]['xwith'].push(xwith);
                                        }
                                        //------- -------rfunc------- -------
                                        rfunc = '        /** \n';
                                        rfunc += '         * '+ rtb.comment +'  \n';
                                        rfunc += '         */\n';
                                        rfunc += '        public function '+rtb.name+'() { \n';
                                        if(rtb.type > 0) { 
                                            if(rtb.ype == 1) { // hasOne 
                                                rfunc += '                return $this->hasOne("'+ rtb.relatetable_class +'","'+rtb.field_relate+'","'+rtb.field_master+'"); \n';
                                            } else {   //HasMany
                                                rfunc += '                return $this->hasMany("'+ rtb.relatetable_class +'","'+rtb.field_relate+'","'+rtb.field_master+'"); \n';
                                            } 
                                            rfunc += '        } \n\n';
                                        }
                                        if(coderelation[table]['func'][rtb.name] != undefined){
                                            coderelation['error'] = true;
                                            coderelation['err'] += rtb.name + ", ";

                                        } else {
                                            coderelation[table]['func'][rtb.name]=rfunc;
                                        }
                                        
                                        //------- -------bfunc------- -------
                                        bfunc +='        /** \n';
                                        bfunc +='        * '+ rtb.comment +' \n';
                                        bfunc +='        */ \n';
                                        bfunc +='        public function '+rtb.bname+'() \n';
                                        bfunc +='        { \n';
                                        bfunc +='                return $this->belongsTo("'+rtb.master_class+'", "'+rtb.field_relate+'", "'+rtb.field_master+'"); \n';
                                        bfunc +='        }         \n\n\n';

                                        if(!coderelation[rtb.relatetable_name]){
                                            coderelation[rtb.relatetable_name]={
                                                xwith:[],
                                                func:{},
                                            }
                                        }
                                        if( coderelation[rtb.relatetable_name]['func'][rtb.bname]){
                                            coderelation['error'] = true;
                                            coderelation['err'] += rtb.bname + ', ';
                                        } else {
                                            coderelation[rtb.relatetable_name]['func'][rtb.bname]=bfunc;
                                        }
                                }
                                })
                            }
                        }) 
                        return coderelation;
                },
                genHtmltable(table){
                    console.log('---gen---html----table----',table.table);
                    let data = {
                        model:{
                            filename: table.modelclass +'.php',
                            doc: "",
                        },
                        service:{
                            filename: table.serviceclass+'.php',
                            doc: "",
                        },
                        controller:{
                            filename: table.controllerclass +'.php',
                            doc: "",
                        },
                        sql:{
                            filename: (new Date().valueOf())+'.sql',
                            doc: "",
                        },
                        vuetm:{
                            filename: table.table +'.js',
                            doc: "",
                        },
                        vue2:{
                            filename: table.table +'.vue',
                            doc: "",
                        },
                        vue3:{
                            filename: table.table +'.vue',
                            doc: "",
                        },
                        svelte:{
                            filename: table.table +'.svelte',
                            doc: "",
                        },
                        routebygen:{
                            filename: "reoutebygen.php",
                            doc: "",
                        },
                    }
                    console.log('---testdata---',data);

                    let pks = this.getpkname(table.pk);
                    let modelcols = this.getmodlecols(table.cols);
                    
                    data.routebygen.doc = '$server->addClass("'+table.controllerclass+'","'+table.baseRoute+'",""); \n';

                    if(table.isModelclass){


                        let relationcode = this.gencoderelation();
                        console.log('gencoderelation-->',relationcode);
                        if(relationcode.error){
                            alert('ERROR! function '+ relationcode.err + ' ซ้ำกันในระบบกรุณตรวจสอบใหม่');
                            return;
                        }


                        html = "<\?php \n";
                        html += '//---------------Model--------------------------------------------'+table.modelclass+'.php\n';
                        html += "";
                        html += '\n';
                        html += '//----------------------------------------------\n';
                        html += '//FILE NAME:  '+table.modelclass+'.php gen for Servit Framework Model\n';
                        html += '//Created by: Tlen<limweb@hotmail.com>\n';
                        html += '//DATE: '+ new Date().toISOString() +'\n';
                        html += '\n';
                        html += '//----------------------------------------------\n';
                        html += 'use Illuminate\\Database\\Eloquent\\Model;\n';
                        html += 'use Illuminate\\Database\\Eloquent\\SoftDeletes;\n';
                        html += 'use Servit\\Restsrv\\Model\\BaseModel;\n';
                        html += '//use DB;\n';
                        html += '\n';
                        html += 'class '+table.modelclass+' extends BaseModel { \n';
                        html += '\n';
                        //-----------soft--delete--------------------------
                        if(table.isSoftdelete){
                            html += '        use SoftDeletes;  \n';
                            if(!table.softdelete){
                                 if(table.cols.filter(col=>col.field == 'deleted_at').length == 0 ){
                                    data.sql.doc += 'ALTER TABLE `'+ table.table+ '` ADD COLUMN `deleted_at` datetime(0) NULL ;';
                                 }
                                 table.softdelete = 'deleted_at';
                            }  
                            html +=  '        const       DELETED_AT =  "' + table.softdelete + '"; \n';
                        }
                        html += '        protected	\$table="'+table.table+'"; \n';
                        html += '        protected	\$primaryKey="'+ pks.join(',')+'";\n';
                        html += '\n';
                        html += '        protected	\$dateFormat = \'U\';\n';
                        html += '        public	    \$timestamps = '+(table.isTimestamps?true:false)+';\n';
                        //----------------timestamp----------------------------
                        if(table.isTimestamps){
                            console.log('----test--date--',table.table,table.createdat,table.updatedat,(table.createdat && table.createdat != 'created_at'));
                            if(table.createdat && table.createdat == 'created_at'){
                            } else {
                                if(table.cols.filter(col=>col.field == 'created_at').length == 0 ){
                                    data.sql.doc +='ALTER TABLE `'+table.table+'` ADD COLUMN `created_at` datetime(0) NULL ;';
                                }
                                table.createdat = 'created_at';
                            }
                            html += '        const       CREATED_AT = \''+table.createdat+'\';\n';
                            if(table.updatedat && table.updatedat == 'updated_at'){
                            } else {
                                if(table.cols.filter(col=>col.field == 'updated_at').length == 0 ){
                                    data.sql.doc  +='ALTER TABLE `'+table.table+'` ADD COLUMN `updated_at` datetime(0) NULL ;';
                                }
                                table.updatedat = 'updated_at';
                            }
                            html += '        const       UPDATED_AT = \''+table.updatedat+'\'; \n';
                        }
                        html += '        \n';
                        html += '        protected	\$guarded = ['+ modelcols.guarded +'];\n';
                        html += '        protected	\$fillable = ['+ modelcols.fillable +'];\n';
                        html += '        protected	\$hidden = ['+ modelcols.hidden +'];\n';
                        html += '        protected	\$appends = [];\n';
                        if( relationcode[table.table]){
                            html += '        protected	\$with = ['+relationcode[table.table]['xwith'].join(' ')+'];\n';
                        } else {
                            html += '        protected	\$with = [];\n';
                        }

                        html += '        protected	\$dates = ['+(table.createdat?'"'+table.createdat+'",':'')+(table.updatedat?'"'+table.updatedat+'",':'')+(table.softdelete?'"'+table.softdelete+'"':'')+'];\n';
                        html += '        protected  \$casts = [ ];\n\n\n';
                        //-------relation function ------------------------------
                        if( relationcode[table.table]){
                            let functions = relationcode[table.table]['func'];
                            Object.keys(functions).map(f=>{
                                html += functions[f] ;
                                html += '\n';
                            })
                        }
                        //-------relation function ------------------------------
                        html += '\n';
                        html += '} \n';
                        html += '\n';
                    } 
                    data.model.doc = html;
                    html = '<\?php \n';
                    html += '//---------------Service------------------------------------------'+table.serviceclass+'.php\n';
                    html += ' \n';
                    html += '//---------------------------------------------- \n';
                    html += '//FILE NAME:  '+table.serviceclass+'.php gen for Servit Framework Service \n';
                    html += '//Created by: Tlen<limweb@hotmail.com> \n';
                    html += '//DATE: '+ new Date().toISOString() +' \n';
                    html += ' \n';
                    html += '//---------------------------------------------- \n';
                    html += 'use \\Servit\\Restsrv\\RestServer\\RestException as TestException; \n';
                    html += 'use \\Servit\\Restsrv\\Traits\\DbTrait as DbTrait; \n';
                    html += 'use \\Servit\\Restsrv\\Service\\BaseService as BaseService; \n';
                    html += 'use \\Servit\\Restsrv\\Service\\BasedbService as BasedbService; \n';
                    html += 'use Illuminate\\Database\\Capsule\\Manager as Capsule; \n';
                    html += ' \n';
                    html += 'class '+table.serviceclass+' extends BaseService \n';
                    html += '{ \n';
                    html += ' \n';
                    html += '    public static function all($member=null) \n';
                    html += '    { \n';
                    html += '        return '+table.modelclass+'::get(); \n';
                    html += '    } \n';
                    html += ' \n';
                    html += '    public static function alliddesc($member=null) \n';
                    html += '    { \n';
                    html += '        return '+table.modelclass+'::orderBy("'+(pks?pks[0]:'id')+'", "desc")->get(); \n';
                    html += '    } \n';
                    html += ' \n';
                    html += '    public static function insert($arrdata,$member) \n';
                    html += '    { \n';
                    html += '        $r = new '+table.modelclass+'(); \n';
                    html += '        $fills = $r->getFillable(); \n';
                    html += '        foreach ($fills as $key) { \n';
                    html += '            if (isset($arrdata[$key])) { \n';
                    html += '                $r->{$key} = $arrdata[$key]; \n';
                    html += '            } \n';
                    html += '        } \n';
                    html += '        $r->save(); \n';
                    html += '        return $r; \n';
                    html += '    } \n';
                    html += ' \n';
                    html += '    public static function insertOrupdate($arrdata,$member) \n';
                    html += '    { \n';
                    html += '        $r = null; \n';
                    html += '        if (isset($arrdata["'+(pks?pks[0]:'id')+'"])) { \n';
                    html += '            $r = '+table.modelclass+'::find($arrdata["'+(pks?pks[0]:'id')+'"]); \n';
                    html += '        } \n';
                    html += '        if (!$r) { \n';
                    html += '            $r = new '+table.modelclass+'(); \n';
                    html += '        } \n';
                    html += ' \n';
                    html += '        $fills = $r->getFillable(); \n';
                    html += '        foreach ($fills as $key) { \n';
                    html += '            if (isset($arrdata[$key])) { \n';
                    html += '                $r->{$key} = $arrdata[$key]; \n';
                    html += '            } \n';
                    html += '        } \n';
                    html += '        $r->save(); \n';
                    html += '        return $r; \n';
                    html += '    } \n';
                    html += ' \n';
                    html += '    public static function getbyid($id,$member=null) \n';
                    html += '    { \n';
                    html += '        $r ='+table.modelclass+'::find($id); \n';
                    html += '        if($r){ \n';
                    html += '            return $r; \n';
                    html += '        } else { \n';
                    html += '            return 0; \n';
                    html += '        } \n';
                    html += '    } \n';
                    html += ' \n';
                    html += '    public static function delete($id,$member=null) \n';
                    html += '    { \n';
                    html += '        $r = '+table.modelclass+'::find($id); \n';
                    html += '        if ($r) { \n';
                    html += '            return $r->delete(); \n';
                    html += '        } else { \n';
                    html += '            return 0; \n';
                    html += '        } \n';
                    html += '    } \n';
                    html += '  \n';
                    html += '  \n';
                    html += '   public static function searchAll($page=1,$perpage=10,$kw="",$ajax=0) { \n ';
                    html += '        $obj = new StdClass(); \n ';
                    html += '        $columns = ['+ modelcols.search +']; //searchColumn \n ';
                    html += '        $kws = []; \n ';
                    html += '        if ($kw) { \n ';
                    html += '            $kws = explode(",", $kw); \n ';
                    html += '        } \n ';
                    html += ' \n ';
                    html += '        $qry = '+table.modelclass+'::query(); \n ';
                    html += '        $qry->whereRaw("1 = 1"); \n ';
                    html += '        $vkw = ""; \n ';
                    html += '        if ($kws) { \n ';
                    html += '            foreach ($kws as $value) { \n ';
                    html += '                $vv = ""; \n ';
                    html += '                @list($k, $v) = explode("=", $value); \n ';
                    html += '                if ($v) { \n ';
                    html += '                    $v1 = str_replace("#", "/", $v); \n ';
                    html += '                    if ($v1) { \n ';
                    html += '                        $v2 = str_replace("@", ".", $v1); \n ';
                    html += '                        $vkw .= $v2 . ","; \n ';
                    html += '                        $vv = $v2; \n ';
                    html += '                    } \n ';
                    html += '                } else { \n ';
                    html += '                    $vv = $k; \n ';
                    html += '                } \n ';
                    html += ' \n ';
                    html += '                if ($k && $v) { \n ';
                    html += '                    $qry->Where($k, "like", "%" . $vv . "%"); \n ';
                    html += '                } else { \n ';
                    html += '                    $qry->where(function($query) use($columns,$vv){ \n ';
                    html += '                        foreach ($columns as $column) { \n ';
                    html += '                            $query->orWhere($column, "like", "%" . $vv . "%"); \n ';
                    html += '                        } \n ';
                    html += '                    }); \n ';
                    html += '                } \n ';
                    html += '            } \n ';
                    html += '        } \n ';
                    html += ' \n ';
                    html += '        $total = $qry->count(); \n ';
                    html += '        $skip = 0; \n ';
                    html += '        if ($total >= 500 || $ajax) { \n ';
                    html += '            if ($ajax == 0) { \n ';
                    html += '                $ajax = 1; \n ';
                    html += '            } \n ';
                    html += '            $skip = ((($page - 1) < 0) ? 0 : $page - 1) * $perpage; \n ';
                    html += '            if ($total < $skip) { \n ';
                    html += '                $skip = 0; \n ';
                    html += '            } \n ';
                    html += '            $datas = $qry->skip($skip)->take($perpage)->get(); \n ';
                    html += '        } else { \n ';
                    html += '            $datas = $qry->get(); \n ';
                    html += '        } \n ';
                    html += ' \n ';
                    html += '        $obj->total = $total; \n ';
                    html += '        $obj->skip = $skip; \n ';
                    html += '        $obj->datas = $datas; \n ';
                    html += '        $obj->skip = $skip; \n ';
                    html += '        $obj->ajax = $ajax; \n ';
                    html += '        $obj->perpage = $perpage; \n ';
                    html += '        $obj->page = $page; \n ';
                    html += '        return $obj; \n ';
                    html += '} \n ';                    
                    html += '  \n';
                    html += '  \n';
                    html += '}  \n';
                    html += ' \n';

                    data.service.doc = html;
                    html = '<\?php \n';
                    html += '//---------------Controller---------------------------------------'+table.controllerclass+'.php\n';
                    html += ' \n';
                    html += '//---------------------------------------------- \n';
                    html += '//FILE NAME:  '+table.controllerclass+'.php gen for Servit Framework Controller \n';
                    html += '//Created by: Tlen<limweb@hotmail.com> \n';
                    html += '//DATE: '+ new Date().toISOString() +'  \n';
                    html += ' \n';
                    html += '//---------------------------------------------- \n';
                    html += 'use	\\Servit\\Restsrv\\RestServer\\RestException; \n';
                    html += 'use	\\Servit\\Restsrv\\RestServer\\RestController as BaseController; \n';
                    html += 'use	Illuminate\Database\\Capsule\\Manager as Capsule; \n';
                    html += 'use	Servit\\Restsrv\\Libs\\Request;  \n';
                    html += 'use	Servit\\Restsrv\\Libs\\Linenotify; \n';
                    html += 'use	Carbon\\Carbon; \n';
                    html += 'use    \\Servit\\Restsrv\\traits\\DbTrait; \n';
                    html += ' \n';
                    html += 'class '+table.controllerclass+'  extends BaseController {   //or JwtController\n';
                    html += '     \n';
                    html += ' \n';
                    html += '   /** \n';
                    html += '     *@noAuth \n';
                    html += '     *@url GET /'+ (table.baseRoute?'all':table.table)+' \n';
                    html += '     *---------------------------------------------- \n';
                    html += '     *FILE NAME:  '+table.controllerclass+' gen for Servit Framework Controller \n';
                    html += '     *DATE:'+ new Date().toISOString() +' \n';
                    html += ' \n';
                    html += '     *---------------------------------------------- \n';
                    html += '     */ \n';
                    html += '    public function alliddesc() \n';
                    html += '    { \n';
                    html += '        try { \n';
                    html += '            // you can add $this->member for Extends JwtController \n';
                    html += '            $datas = '+table.serviceclass+'::alliddesc(); \n';
                    html += '            return [ \n';
                    html += '                "datas" => $datas, \n';
                    html += '                "status" => "1", \n';
                    html += '                "success" => true, \n';
                    html += '            ]; \n';
                    html += '        } catch (Exception $e) { \n';
                    html += '            return [ \n';
                    html += '                "status" => "0", \n';
                    html += '                "success" => false, \n';
                    html += '                "msg" => $e->getMessage(), \n';
                    html += '            ]; \n';
                    html += '        } \n';
                    html += '    } \n';
                    html += ' \n';
                    html += '    /** \n';
                    html += '     *@ noAuth \n';
                    html += '     *@url POST '+ (table.baseRoute?'': '/'+table.table)+'/update \n';
                    html += '     *---------------------------------------------- \n';
                    html += '     *FILE NAME:  '+table.controllerclass+' gen for Servit Framework Controller \n';
                    html += '     *Created by: Tlen<limweb@hotmail.com> \n';
                    html += '     *DATE: '+ new Date().toISOString() +' \n';
                    html += ' \n';
                    html += '     *---------------------------------------------- \n';
                    html += '    * Example: \n';
                    html += '    * <code>   \n';
                    html += '    * {    \n';
                    table.cols.forEach(col =>{
                        if(col.isEdit){
                            html += '    *     "'+col.field+'": "'+col.field+'"  \n';
                        }
                    });
                    html += '    * }    \n';
                    html += '    * </code>  \n';
                    html += '     */ \n';
                    html += '    public function update() \n';
                    html += '    { \n';
                    html += '        try { \n';
                    html += '            // you can add $this->member for Extends JwtController \n';
                    html += '            $rs = '+table.serviceclass+'::insertOrupdate($this->input->input->toArray()); \n';
                    html += '            $datas = '+table.serviceclass+'::alliddesc(); \n';
                    html += '            return [ \n';
                    html += '                "datas" => $datas, \n';
                    html += '                "status" => $rs, \n';
                    html += '                "success" => $rs, \n';
                    html += '            ]; \n';
                    html += '        } catch (Exception $e) { \n';
                    html += '            return [ \n';
                    html += '                "status" => "0", \n';
                    html += '                "success" => false, \n';
                    html += '                "msg" => $e->getMessage(), \n';
                    html += '            ]; \n';
                    html += '        } \n';
                    html += '    } \n';
                    html += ' \n';
                    html += '    /** \n';
                    html += '     *@ noAuth \n';
                    html += '     *@url POST '+ (table.baseRoute?'': '/'+table.table)+'/add \n';
                    html += '     *---------------------------------------------- \n';
                    html += '     *FILE NAME:  '+table.controllerclass+' gen for Servit Framework Controller \n';
                    html += '     *Created by: Tlen<limweb@hotmail.com> \n';
                    html += '     *DATE: '+ new Date().toISOString() +' \n';
                    html += ' \n';
                    html += '     *---------------------------------------------- \n';
                    html += '    * Example: \n';
                    html += '    * <code>   \n';
                    html += '    * {    \n';
                    table.cols.forEach(col =>{
                        if(col.isCreate){
                            html += '    *     "'+col.field+'": "'+col.field+'"  \n';
                        }
                    });
                    html += '    * }    \n';
                    html += '    * </code>  \n';                    
                    html += '     */ \n';
                    html += '    public function add() \n';
                    html += '    { \n';
                    html += '        try { \n';
                    html += '            // you can add $this->member for Extends JwtController \n';
                    html += '            $rs = '+table.serviceclass+'::insert($this->input->input->toArray()); \n';
                    html += '            $datas = '+table.serviceclass+'::alliddesc(); \n';
                    html += '            return [ \n';
                    html += '                "datas" => $datas, \n';
                    html += '                "status" => "1", \n';
                    html += '                "success" => true, \n';
                    html += '            ]; \n';
                    html += '        } catch (Exception $e) { \n';
                    html += '            return [ \n';
                    html += '                "status" => "0", \n';
                    html += '                "success" => false, \n';
                    html += '                "msg" => $e->getMessage(), \n';
                    html += '            ]; \n';
                    html += '        } \n';
                    html += '    } \n';
                    html += ' \n';
                    html += '    /** \n';
                    html += '     *@ noAuth \n';
                    html += '     *@url GET '+ (table.baseRoute?'': '/'+table.table)+'/del/$id \n';
                    html += '     *---------------------------------------------- \n';
                    html += '     *FILE NAME:  '+table.controllerclass+'.php gen for Servit Framework Controller \n';
                    html += '     *Created by: Tlen<limweb@hotmail.com> \n';
                    html += '     *DATE:'+ new Date().toISOString() +' \n';
                    html += ' \n';
                    html += '     *---------------------------------------------- \n';
                    html += '     */ \n';
                    html += '    public function del($id) \n';
                    html += '    { \n';
                    html += '        try { \n';
                    html += '            // you can add $this->member for Extends JwtController \n';
                    html += '            $rs = '+table.serviceclass+'::delete($id); \n';
                    html += '            $datas = '+table.serviceclass+'::alliddesc(); \n';
                    html += '            return [ \n';
                    html += '                "deleted" => $rs, \n';
                    html += '                "datas" => $datas, \n';
                    html += '                "status" => "1", \n';
                    html += '                "success" => true, \n';
                    html += '            ]; \n';
                    html += '        } catch (Exception $e) { \n';
                    html += '            return [ \n';
                    html += '                "status" => "0", \n';
                    html += '                "success" => false, \n';
                    html += '                "msg" => $e->getMessage(), \n';
                    html += '            ]; \n';
                    html += '        } \n';
                    html += '    } \n';
                    html += '/** \n';
                    html += '*@noAuth \n';
                    html += '*@url GET '+ (table.baseRoute?'': '/'+table.table)+'/getby/$id \n';
                    html += '*---------------------------------------------- \n';
                    html += '*FILE NAME:  '+ table.controllerclass +' gen for Servit Framework Controller \n';
                    html += '*Created by: Tlen<limweb@hotmail.com> \n';
                    html += '*DATE:  '+ new Date().toISOString() +'  \n';
                    html += ' \n';
                    html += '*---------------------------------------------- \n';
                    html += '*/ \n';
                    html += 'public function getbyid($id){ \n';
                    html += '    try { \n';
                    html += '        // you can add $this->member for Extends JwtController \n';
                    html += '        $datas = '+table.serviceclass+'::getbyid($id); \n';
                    html += '        return [ \n';
                    html += '            "datas" => $datas, \n';
                    html += '            "status" => "1", \n';
                    html += '            "success"=> true, \n';
                    html += '        ]; \n';
                    html += '    } catch (Exception $e) { \n';
                    html += '        return[ \n';
                    html += '            "status" => "0", \n';
                    html += '            "success"=> false, \n';
                    html += '            "msg"=> $e->getMessage(), \n';
                    html += '        ];  \n';
                    html += '    } \n';
                    html += '}  \n';
                    html += '/** \n';
                    html += ' *@noAuth \n';
                    html += ' *@url GET '+ (table.baseRoute?'': '/'+table.table)+'/getall/ \n';
                    html += ' *@url GET '+ (table.baseRoute?'': '/'+table.table)+'/getall/$page \n';
                    html += ' *@url GET '+ (table.baseRoute?'': '/'+table.table)+'/getall/$page/$perpage \n';
                    html += ' *@url GET '+ (table.baseRoute?'': '/'+table.table)+'/getall/$page/$perpage/$ajax \n';
                    html += ' *@url GET '+ (table.baseRoute?'': '/'+table.table)+'/getall/$page/$perpage/$ajax/$kw \n';
                    html += ' */ \n';
                    html += 'public function all($page = 1, $perpage = 10, $kw = "", $ajax = 0){ \n';
                    html += '        $obj = '+ table.serviceclass+'::searchAll($page,$perpage,$kw,$ajax); \n';
                    html += '        $data = [ \n';
                    html += '            "ajax" => $obj->ajax, \n';
                    html += '            "status" => "1", \n';
                    html += '            "page" => $obj->page, \n';
                    html += '            "perpage" => $obj->perpage, \n';
                    html += '            "skip" => $obj->skip, \n';
                    html += '            "total" => $obj->total, \n';
                    html += '            "datacount" =>count($obj->datas), \n';
                    html += '            "datas" => $obj->datas, \n';
                    html += '        ]; \n';
                    html += '        // dump($data); \n';
                    html += '        return $data; \n';
                    html += ' \n';
                    html += '} \n';
                    html += '} \n';
                    html += ' \n';
                    html += ' \n';
                    data.controller.doc = html;

                    html = 'export default { \n';
                    html += '    template: \`\`, \n';
                    html += '    name: \'\', \n';
                    html += '    mixins: [], \n';
                    html += '    data() { \n';
                    html += '        return { \n';
                    html += '            theme: "AdminLte" \n';
                    html += '        }; \n';
                    html += '    }, \n';
                    html += '    created() { \n';
                    html += '        console.log( this.$option.name + "component is created"); \n';
                    html += '    }, \n';
                    html += '    mounted() {}, \n';
                    html += '    methods: {}, \n';
                    html += '    computed: {}, \n';
                    html += '    components:{} \n';
                    html += '};                     \n';
                    data.vuetm.doc = html;
                    html ='<template> \n';
                    html +='    <div> \n';
                    html +=' \n';
                    html +='    </div> \n';
                    html +='</template> \n';
                    html +=' \n';
                    html +='<script\> \n';
                    html +='    export default { \n';
                    html +='         \n';
                    html +='    } \n';
                    html +='</script\> \n';
                    html +=' \n';
                    html +='<style lang="css" scoped> \n';
                    html +=' \n';
                    html +='</style> \n';
                    data.vue2.doc = html;

                    html = '<template> \n';
                    html += '  <div><h1>Test123459</h1></div> \n';
                    html += '</template> \n';
                    html += ' \n';
                    html += '<script\> \n';
                    html += 'import { onMounted, defineComponent} from "vue" \n';
                    html += 'const test =  defineComponent({ \n';
                    html += '  setup() { \n';
                    html += '    onMounted(() => { \n';
                    html += '      console.log("--onMounte-- test"); \n';
                    html += '    }) \n';
                    html += '    return {  } \n';
                    html += '  }, \n';
                    html += '}); \n';
                    html += 'console.log("--test--",test); \n';
                    html += 'export default test; \n';
                    html += '</script\> \n';
                    data.vue3.doc = html;

                    html = '<script\> \n';
                    html += '	export let name; \n';
                    html += '</script\> \n';
                    html += ' \n';
                    html += '<main> \n';
                    html += '	<h1>Hello {name}!</h1> \n';
                    html += '	<p>Visit the <a href="https://svelte.dev/tutorial">Svelte tutorial</a> to learn how to build Svelte apps.</p> \n';
                    html += '</main> \n';
                    html += ' \n';
                    html += '<style> \n';
                    html += '	main { \n';
                    html += '		text-align: center; \n';
                    html += '		padding: 1em; \n';
                    html += '		max-width: 240px; \n';
                    html += '		margin: 0 auto; \n';
                    html += '	} \n';
                    html += '	h1 { \n';
                    html += '		color: #ff3e00; \n';
                    html += '		text-transform: uppercase; \n';
                    html += '		font-size: 4em; \n';
                    html += '		font-weight: 100; \n';
                    html += '	} \n';
                    html += '	@media (min-width: 640px) { \n';
                    html += '		main { \n';
                    html += '			max-width: none; \n';
                    html += '		} \n';
                    html += '	} \n';
                    html += '</style> \n';
                    data.svelte.doc = html;
                    return data;
                },
                getpkname(pks){
                    return pks.map(pk=>pk.Column_name)
                },
                getmodlecols(cols){
                    let append = '';
                    let fillable='';
                    let guarded = '';
                    let hidden  = '';
                    let search  = '';
                    cols.map(col=>{
                        if(col.isCol && col.isShow){
                            append += '"'+col.field+'",';
                        } else {
                            hidden += '"'+col.field+'",';
                        }
                        if(col.isCol && col.isCreate){
                            fillable += '"'+col.field+'",';
                        }
                        if(col.isCol && col.isGuarded){
                            guarded += '"'+col.field+'",';
                        }

                        if(col.isSearch) {
                            search += '"'+col.field+'",';
                        }
                    });
                    return {
                        append:append,
                        fillable:fillable,
                        guarded:guarded,
                        hidden:hidden,
                        search:search,
                    };
                },
                genbytable(table){
                    console.log('---gen by table',table);
                    this.codedata = [];
                    let cols = table.cols.filter(col=>col.isCol);
                    if(cols.length > 0 ){
                       this.codegen = '';
                       let tabledata = this.genHtmltable(table);
                       this.codegen  += tabledata.model.doc;
                       this.codegen  += tabledata.service.doc;
                       this.codegen  += tabledata.controller.doc;
                       this.codegen  += tabledata.vuetm.doc;
                       this.codegen  += tabledata.vue2.doc;
                       this.codegen  += tabledata.vue3.doc;
                       this.codegen  += tabledata.svelte.doc;
                       this.codegen  += "//---------------------------------------------------sql \n";
                       this.codegen  += tabledata.sql.doc;
                       this.codegen  += "//---------------------------------------------------routebygen.php \n";
                       this.codegen  += tabledata.routebygen.doc;
                       this.codegen += '\n\n';
                       this.codedata.push(tabledata);
                       
                       let fileName = 'codegen_'+table.table+'.zip';
                       this.genzipfile(fileName)
                    } else {
                        alert('Please Select Column minimum 1 column!');
                    }
                    location.href="#codegen"
                },
                genall(){
                    let data = this.tables.filter(table=>table.isTable);
                    console.log('genall table--->',data);
                    this.genAllHtmltable(data);
                    let fileName = 'codegen_vuevm.zip';
                    this.genzipfile(fileName);
                    if(this.isGenbckend){
                        let postdata = { 
                            tabledata:this.codedata,
                            logindata:this.codelogindata 
                            };
                        console.log('---postdata--->',postdata);
                        axios.post("/gencode/genall",JSON.stringify(postdata))
                        .then((rs) => {
                            console.log(rs);
                        })
                        .catch( (err) => {
                            console.log(err);
                        });
                    }

                    location.href="#codegen"
                },
                genlogin(){
                    console.log('---loginsys----',this.loginsys);
                    this.codegen = '';
                    let logincode = this.genlogincode();
                    Object.keys(logincode).map(key=>{
                        this.codegen += logincode[key];
                    })
                    let fileName = 'codegen_login.zip';
                    this.codelogindata = logincode;
                    this.genzipfile(fileName)
                },
                genzipfile(filename) {
                    let d = new Date()
                    let fileName = ( d.getFullYear()+'-'+ d.getMonth() + '-'+ d.getDate() + d.getMilliseconds() )+'_'+filename;

                    console.log('-----start---gen---zip---file----',fileName,this.isDownload);
                    if(this.isDownload){
                        var zip = new JSZip();
                        let routedata = '';
                        this.codedata.map(tabledata=>{
                                zip.file("models/"+tabledata.model.filename,tabledata.model.doc);
                                zip.file("services/"+tabledata.service.filename,tabledata.service.doc);
                                zip.file("controllers/"+tabledata.controller.filename,tabledata.controller.doc);
                                zip.file("sql/"+tabledata.sql.filename,tabledata.sql.doc);
                                zip.file("vuejs/tm/"+tabledata.vuetm.filename,tabledata.vuetm.doc);
                                zip.file("vuejs/vue2/"+tabledata.vue2.filename,tabledata.vue2.doc);
                                zip.file("vuejs/vue3/"+tabledata.vue3.filename,tabledata.vue3.doc);
                                zip.file("svelte/"+tabledata.svelte.filename,tabledata.svelte.doc);
                                routedata +=  tabledata.routebygen.doc;
                        })
                        if(this.codelogindata.model){
                            zip.file("models/Login.php",this.codelogindata.model);
                        }
                        if(this.codelogindata.jwtsrv){
                            zip.file("services/JwtService.php",this.codelogindata.jwtsrv );
                        }
                        if(this.codelogindata.loginsrv){
                            zip.file("services/LoginService.php",this.codelogindata.loginsrv );
                        }
                        if(this.codelogindata.loginctl){
                            zip.file("controllers/LoginController.php",this.codelogindata.loginctl);
                            routedata  = '$server->addClass("LoginController","",""); \n' + routedata;
                        }
                        if(this.codelogindata.jwtctl){
                            zip.file("controllers/JwtController.php",this.codelogindata.jwtctl);
                            routedata  = '$server->addClass("JwtController","",""); \n' + routedata;
                        }
                        if(this.codelogindata.vuehtml){
                            zip.file("/view/page/Login.vue",this.codelogindata.vuehtml);
                        }
                        zip.file("route/routebygen.php",routedata);

                        zip.generateAsync({type:"blob"}).then(
                        (content)=>{
                            const a = document.createElement("a");
                            a.style.display = "none";
                            document.body.appendChild(a);
                            a.href = window.URL.createObjectURL(
                                new Blob([content],{type: "application/zip"})
                            );
                            a.setAttribute("download", fileName);
                            a.click();
                            window.URL.revokeObjectURL(a.href);
                            document.body.removeChild(a);
                            console.log('----gen zip end-----');
                        },
                        (err) =>{
                            console.log('genzip-err--->',err)
                        });
                    }
                },
                pluralize(val, word, plural = word + 's'){
                    const _pluralize = (num, word, plural = word + 's') =>[1, -1].includes(Number(num)) ? word : plural;
                    if (typeof val === 'object') return (num, word) => _pluralize(num, word, val[word]);
                    return _pluralize(val, word, plural);
                },
                selectrelatetb(related){
                    related.field_relate='';
                    let i = (related.type==0 || related.type == 2 ) ? 2 : 1;
                    if(related.relatetable_name){
                        related.name = this.pluralize(i,related.relatetable_name);
                    }
                    let tb = this.tables.find(table=>{
                        if(table.table == related.relatetable_name){
                            console.log('---table---',table.modelclass);
                            return table;        
                        }
                    });
                    console.log('---tb---',tb);
                    if(tb){
                        related.relatetable_class =  tb.modelclass;
                    }
                },
                genlogincode(){
                    let data = {    
                        model:'',
                        jwtsrv: '',
                        loginsrv: '',
                        jwtctl: '',
                        loginctl: '',
                        vuehtml: '',
                    }
                    if(!this.loginsys.table){
                        return data;
                    }
                    //-----------------------------------------------------------  Login.php Model
                    let model =   '<\?php \n';
                    model +=  'use Illuminate\\Database\\Eloquent\\Model; \n';
                    model +=  'use Illuminate\\Database\\Eloquent\\SoftDeletes; \n';
                    model +=  'use Servit\\Restsrv\\Model\\BaseModel; \n';
                    model +=  ' \n';
                    model +=  'class Login extends BaseModel \n';
                    model +=  '{ \n';
                    model += '        protected	\$table="'+this.loginsys.table+'"; \n';
                    model +=  ' \n';
                    model +=  '}  \n';
                    data.model = model;
                    //-----------------------------------------------------------  JwtService.php
                    let jwtsrv = '<\?php \n';
                    jwtsrv += ' \n';
                    jwtsrv += ' \n';
                    jwtsrv += '//---------------------------------------------- \n';
                    jwtsrv += '//FILE NAME:  JwtService.php gen for Servit Framework Service \n';
                    jwtsrv += '//DATE: 2019-05-03(Fri)  07:48:13  \n';
                    jwtsrv += ' \n';
                    jwtsrv += '//---------------------------------------------- \n';
                    jwtsrv += 'use \\Servit\\Restsrv\\RestServer\\RestException as TestException; \n';
                    jwtsrv += 'use \\Servit\\Restsrv\\Service\\BaseService as BaseService; \n';
                    jwtsrv += 'use Illuminate\\Database\\Capsule\\Manager as Capsule; \n';
                    jwtsrv += 'use Lcobucci\\JWT\\Signer\\Hmac\Sha256; \n';
                    jwtsrv += 'use Lcobucci\\JWT\\Signer; \n';
                    jwtsrv += 'use Lcobucci\\JWT\\Parser; \n';
                    jwtsrv += 'use Lcobucci\\JWT\\ValidationData; \n';
                    jwtsrv += 'use Lcobucci\\JWT\\Builder; \n';
                    jwtsrv += 'use Lcobucci\\JWT\\Token; \n';
                    jwtsrv += ' \n';
                    jwtsrv += ' \n';
                    jwtsrv += 'class JwtService extends BaseService \n';
                    jwtsrv += '{ \n';
                    jwtsrv += ' \n';
                    jwtsrv += '    private static $member = null; \n';
                    jwtsrv += ' \n';
                    jwtsrv += '    public static  function  getToken($user = null) \n';
                    jwtsrv += '    { \n';
                    jwtsrv += '        if (!$user) return []; \n';
                    jwtsrv += '            $secret = $_ENV["APP_KEY"]; \n';
                    jwtsrv += '            $header = [ \n';
                    jwtsrv += '                "alg"   => "HS256", \n';
                    jwtsrv += '                "typ"   => "JWT" \n';
                    jwtsrv += '            ]; \n';
                    jwtsrv += '            $payload = [ \n';
                    jwtsrv += '                "iss"       => $user->'+this.loginsys.iss+', \n';
                    jwtsrv += '                "sub"       => $user->'+this.loginsys.username+', \n';
                    jwtsrv += '                "aud"       => $user->'+this.loginsys.aud+', \n';
                    jwtsrv += '                "name"      => $user->'+this.loginsys.name+', \n';
                    if(this.loginsys.exp){
                        jwtsrv += '                "exp"      => '+(this.loginsys.exp * 60 * 100 )+', \n';
                    }
                    jwtsrv += '                "uid"       => $user->id, \n';
                    jwtsrv += '            ]; \n';
                    jwtsrv += '            $jwt = self::generateJWT("sha256", $header, $payload, $secret); \n';
                    jwtsrv += '            return $jwt; \n';
                    jwtsrv += '    } \n';
                    jwtsrv += ' \n';
                    jwtsrv += '    public static function verify($jwt) \n';
                    jwtsrv += '    {    \n';
                    jwtsrv += '        $str_jwt = (string)$jwt; \n';
                    jwtsrv += '        if ($str_jwt) { \n';
                    jwtsrv += '            $secret = $_ENV["APP_KEY"]; \n';
                    jwtsrv += '            $verify = self::verifyJWT("sha256", $str_jwt, $secret); \n';
                    jwtsrv += '            if ($verify) { \n';
                    jwtsrv += '                return true; \n';
                    jwtsrv += '            } else { \n';
                    jwtsrv += '                return false; \n';
                    jwtsrv += '            } \n';
                    jwtsrv += '        } else { \n';
                    jwtsrv += '            return false; \n';
                    jwtsrv += '        } \n';
                    jwtsrv += '    } \n';
                    jwtsrv += ' \n';
                    jwtsrv += '    private static function base64UrlEncode($data) \n';
                    jwtsrv += '    { \n';
                    jwtsrv += '        $data = (string)$data; \n';
                    jwtsrv += '        $urlSafeData = strtr(base64_encode($data), "+/", "-_"); \n';
                    jwtsrv += '        return rtrim($urlSafeData, "="); \n';
                    jwtsrv += '    } \n';
                    jwtsrv += ' \n';
                    jwtsrv += '    private static function base64UrlDecode($data) \n';
                    jwtsrv += '    { \n';
                    jwtsrv += '        $urlUnsafeData = strtr($data, "-_", "+/"); \n';
                    jwtsrv += '        $paddedData = str_pad($urlUnsafeData, strlen($data) % 4, "=", STR_PAD_RIGHT); \n';
                    jwtsrv += '        return base64_decode($paddedData); \n';
                    jwtsrv += '    } \n';
                    jwtsrv += ' \n';
                    jwtsrv += '    private static function getOpenSSLErrors() \n';
                    jwtsrv += '    { \n';
                    jwtsrv += '        $messages = []; \n';
                    jwtsrv += '        while ($msg = openssl_error_string()) { \n';
                    jwtsrv += '            $messages[] = $msg; \n';
                    jwtsrv += '        } \n';
                    jwtsrv += '        return $messages; \n';
                    jwtsrv += '    } \n';
                    jwtsrv += ' \n';
                    jwtsrv += '    public static function generateJWT($algo, $header, $payload, $secret) \n';
                    jwtsrv += '    { \n';
                    jwtsrv += '        $str_header = json_encode($header); \n';
                    jwtsrv += '        $str_payload = json_encode($payload); \n';
                    jwtsrv += '        $headerEncoded = self::base64UrlEncode($str_header); \n';
                    jwtsrv += '        $payloadEncoded = self::base64UrlEncode($str_payload); \n';
                    jwtsrv += '        $dataEncoded = "$headerEncoded.$payloadEncoded"; \n';
                    jwtsrv += '        $signature = hash_hmac($algo, $dataEncoded, $secret, true); \n';
                    jwtsrv += '        $signatureEncoded = self::base64UrlEncode($signature); \n';
                    jwtsrv += '        $jwt  = "$dataEncoded.$signatureEncoded"; \n';
                    jwtsrv += '        return [ \n';
                    jwtsrv += '            "token" => $jwt \n';
                    jwtsrv += '        ]; \n';
                    jwtsrv += '    } \n';
                    jwtsrv += ' \n';
                    jwtsrv += '    private static function verifyJWT($algo,  $jwt,  $secret) \n';
                    jwtsrv += '    { \n';
                    jwtsrv += '        if (empty($jwt)) return false; \n';
                    jwtsrv += '        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode(".", $jwt); \n';
                    jwtsrv += '        $dataEncoded  = "$headerEncoded.$payloadEncoded"; \n';
                    jwtsrv += '        $signature = self::base64UrlDecode($signatureEncoded); \n';
                    jwtsrv += '        $rawSignature = hash_hmac($algo, $dataEncoded, $secret, true); \n';
                    jwtsrv += '        $result = hash_equals($rawSignature, $signature); \n';
                    jwtsrv += '        return $result; \n';
                    jwtsrv += '    } \n';
                    jwtsrv += ' \n';
                    jwtsrv += '    public function getUser($jwt) { \n';
                    jwtsrv += '        if (empty($jwt)) return false; \n';
                    jwtsrv += '        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode(".", $jwt); \n';
                    jwtsrv += '        $data = self::base64UrlDecode($payloadEncoded); \n';
                    jwtsrv += '        $data = json_decode($data); \n';
                    jwtsrv += '        $uid =  isset($data->uid ) ? $data->uid : null; \n';
                    jwtsrv += '        if($uid){ \n';
                    jwtsrv += '                $member = Login::find($uid); \n';
                    jwtsrv += '                self::$member = $member; \n';
                    jwtsrv += '                return $member; \n';
                    jwtsrv += '        } else { \n';
                    jwtsrv += '              return false; \n';
                    jwtsrv += '        } \n';
                    jwtsrv += '    } \n';
                    jwtsrv += ' \n';
                    jwtsrv += '    private static function jwtdata(string $algo, string $jwt, string $secret) \n';
                    jwtsrv += '    { \n';
                    jwtsrv += '        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode(".", $jwt); \n';
                    jwtsrv += '        $data = self::base64UrlDecode($payloadEncoded); \n';
                    jwtsrv += '        $data = json_decode($data); \n';
                    jwtsrv += '        $member = Member::find($data->uid); \n';
                    jwtsrv += '        return  $member; \n';
                    jwtsrv += '    } \n';
                    jwtsrv += '} \n';
                    jwtsrv += '  \n';
                    data.jwtsrv = jwtsrv;

                    //-----------------------------------------------------------  LoginService.php
                    let loginsrv = '<\?php \n';
                    loginsrv += ' \n';
                    loginsrv += 'use \\Servit\\Restsrv\\Service\\BaseService as BaseService; \n';
                    loginsrv += 'use Illuminate\\Database\\Capsule\\Manager as Capsule; \n';
                    loginsrv += ' \n';
                    loginsrv += 'class LoginService  extends BaseService \n';
                    loginsrv += '{ \n';
                    loginsrv += '    public static function login($arrdata){ \n';
                    loginsrv += '        $user = Login::where("'+this.loginsys.username+'",$arrdata["'+this.loginsys.username+'"])->where("'+this.loginsys.password+'",$arrdata["'+this.loginsys.password+'"])->first(); \n';
                    loginsrv += '        if($user){ \n';
                    loginsrv += '            return $user; \n';
                    loginsrv += '        } else { \n';
                    loginsrv += '            return false; \n';
                    loginsrv += '        } \n';
                    loginsrv += '    } \n';
                    loginsrv += '}  \n';
                    loginsrv += '   \n';
                    data.loginsrv =  loginsrv;


                    //------------------------------------------------------------ JwtController.php  
                    let jwtctl = '<\?php \n';
                    jwtctl += 'use \\Servit\\Restsrv\\RestServer\\RestController as BaseController; \n';
                    jwtctl += ' \n';
                    jwtctl += 'class JwtController extends BaseController \n';
                    jwtctl += '{ \n';
                    jwtctl += ' \n';
                    jwtctl += '    public $member = null; \n';
                    jwtctl += ' \n';
                    jwtctl += '    public function authorize() \n';
                    jwtctl += '    { \n';
                    jwtctl += '        try { \n';
                    jwtctl += '            $token = $this->input->token; \n';
                    jwtctl += '            $jwt = new JwtService(); \n';
                    jwtctl += '            $rs = $jwt->verify($token); \n';
                    jwtctl += '            if($rs){ \n';
                    jwtctl += '                $this->member = $jwt->getUser($token); \n';
                    jwtctl += '                $this->server->setStatus(200); \n';
                    jwtctl += '                return true; \n';
                    jwtctl += '            } else { \n';
                    jwtctl += '                $this->server->setStatus(401); \n';
                    jwtctl += '                return false; \n';
                    jwtctl += '            } \n';
                    jwtctl += '        } catch (Exception $e) { \n';
                    jwtctl += '            $this->server->setStatus(401); \n';
                    jwtctl += '            return false; \n';
                    jwtctl += '        } \n';
                    jwtctl += '    } \n';
                    jwtctl += ' \n';
                    jwtctl += '} \n';
                    jwtctl += ' \n';     

                    data.jwtctl= jwtctl;               
                    //-----------------------------------------------------------  LoginController.php

                    let loginctl = '<\?php \n';
                    loginctl += ' \n';
                    loginctl += 'use	\\Servit\\Restsrv\\RestServer\\RestException; \n';
                    loginctl += 'use	\\Servit\\Restsrv\\RestServer\\RestController as BaseController; \n';
                    loginctl += 'use	Illuminate\\Database\\Capsule\\Manager as Capsule; \n';
                    loginctl += 'use	Servit\\Restsrv\\Libs\\Request;  \n';
                    loginctl += 'use	Servit\\Restsrv\\Libs\\Linenotify; \n';
                    loginctl += 'use	Carbon\\Carbon; \n';
                    loginctl += 'use \\Servit\\Restsrv\\traits\\DbTrait; \n';
                    loginctl += ' \n';
                    loginctl += 'class LoginController  extends JwtController { \n';
                    loginctl += '     \n';
                    loginctl += '    /** \n';
                    loginctl += '    *@noAuth \n';
                    loginctl += '    *@url POST /login \n';
                    loginctl += '    *---------------------------------------------- \n';
                    loginctl += '    *FILE NAME:  LoginController.php gen for Servit Framework Controller \n';
                    loginctl += '    *---------------------------------------------- \n';
                    loginctl += '    */ \n';
                    loginctl += '    public function login(){ \n';
                    loginctl += '        try { \n';
                    loginctl += '            $arrdata = $this->input->input->toArray(); \n';
                    loginctl += '            $user = LoginService::login($arrdata); \n';
                    loginctl += '            $jwt = JwtService::getToken($user); \n';
                    loginctl += '            return [ \n';
                    loginctl += '                "user" => $user, \n';
                    loginctl += '                "jwt" => $jwt, \n';
                    loginctl += '                "status" => "1", \n';
                    loginctl += '                "success"=> true, \n';
                    loginctl += '            ]; \n';
                    loginctl += '        } catch (Exception $e) { \n';
                    loginctl += '            return[ \n';
                    loginctl += '                "status" => "0", \n';
                    loginctl += '                "success"=> false, \n';
                    loginctl += '                "msg"=> $e->getMessage(), \n';
                    loginctl += '            ];  \n';
                    loginctl += '        } \n';
                    loginctl += '    } \n';
                    loginctl += ' \n';
                    loginctl += '} \n';
                    loginctl += '  \n';                    

                    data.loginctl = loginctl;
                    //-----------------------------------------------------------  Login.vue 
                    let vuehtml = '<template> \n';
                    vuehtml += '    <div> \n';
                    vuehtml += '        <form> \n';
                    vuehtml += '            <input type="text" placeholder="User" v-model="'+this.loginsys.username+'" /> \n';
                    vuehtml += '            <input type="password" placeholder="password" v-model="'+this.loginsys.password+'" /> \n';
                    vuehtml += '            <input type="submit" value="Sign In"  @click="login" /> \n';
                    vuehtml += '            <input type="reset" value="Clear"  /> \n';
                    vuehtml += '        </form> \n';
                    vuehtml += '    </div> \n';
                    vuehtml += '</template> \n';
                    vuehtml += '<script\> \n';
                    vuehtml += '    export default { \n';
                    vuehtml += '        data(){ \n';
                    vuehtml += '            return { \n';
                    vuehtml += '                '+this.loginsys.username+':"", \n';
                    vuehtml += '                '+this.loginsys.password+':"" \n';
                    vuehtml += '            } \n';
                    vuehtml += '        }, \n';
                    vuehtml += '        methods:{ \n';
                    vuehtml += '            login(){ \n';
                    vuehtml += '                console.log("--login-----"); \n';
                    vuehtml += '                axios.post("/login",{ '+this.loginsys.username+': this.'+this.loginsys.username+','+this.loginsys.password+':this.'+this.loginsys.password+' }) \n';
                    vuehtml += '                .then((rs) => { \n';
                    vuehtml += '                    console.log(rs); \n';
                    vuehtml += '                    const { user,jwt} = rs.data; \n';
                    vuehtml += '                    console.log("--login-user--", user); \n';
                    vuehtml += '                    if (user && jwt) { \n';
                    vuehtml += '                        console.log("---login-----"); \n';
                    vuehtml += '                        this.$ls.set("user", user); \n';
                    vuehtml += '                        //this.$store.state.user = user; \n';
                    vuehtml += '                        this.$ls.set("jwt",jwt.token); \n';
                    vuehtml += '                        //this.$store.state.jwt = jwt.token; \n';
                    vuehtml += '                        if(jwt.token) { \n';
                    vuehtml += '                            axios.defaults.headers.common["authorization"] = `Bearer ${jwt.token}`; \n';
                    vuehtml += '                        } else { \n';
                    vuehtml += '                            delete axios.defaults.headers.common["authorization"]; \n';
                    vuehtml += '                        } \n';
                    vuehtml += '                        this.$router.push("/"); \n';
                    vuehtml += '                        this.'+this.loginsys.username+' = ""; \n';
                    vuehtml += '                        this.'+this.loginsys.password+' = ""; \n';
                    vuehtml += '                    } else { \n';
                    vuehtml += '                        alert("username/password ไม่ถูกต้อง"); \n';
                    vuehtml += '                    } \n';                        
                    vuehtml += '                }) \n';
                    vuehtml += '                .catch( (err) => { \n';
                    vuehtml += '                    console.log(err); \n';
                    vuehtml += '                     \n';
                    vuehtml += '                }); \n';
                    vuehtml += '            } \n';
                    vuehtml += '        }, \n';
                    vuehtmml =+ '       created(){ \n';
                    vuehtmml =+ '           const jwt = this.$ls.get("jwt"); \n';
                    vuehtmml =+ '           if(jwt ) { \n';
                    vuehtmml =+ '               axios.defaults.headers.common["authorization"] = `Bearer ${jwt}`; \n';
                    vuehtmml =+ '           } \n';
                    vuehtmml =+ '       }, \n';                    
                    vuehtml += '    } \n';
                    vuehtml += '</script\> \n';
                    vuehtml += '<style lang="css" scoped> \n';
                    vuehtml += '</style> \n';
                    vuehtml += ' \n';
                    data.vuehtml = vuehtml;
                    //-----------------------------------------------------------  End.
                    return data;
                }
            },
            mounted(){
                mybutton = document.getElementById("myBtn");
                // When the user scrolls down 20px from the top of the document, show the button
                window.onscroll = function() {scrollFunction()};
                function scrollFunction() {
                    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                        mybutton.style.display = "block";
                    } else {
                        mybutton.style.display = "none";
                    }
                }
                this.$nextTick(()=>{
                        let relationtables = this.$ls.get('relations');
                        console.log('---on nextTick--',relationtables);
                        if( relationtables != null ) {
                            this.relationtables = relationtables;
                        }
                        this.tables.map(table=>{
                            if(this.relationtables[table.table] != undefined ){
                                table.relations = this.relationtables[table.table];
                            } else {
                                this.relationtables[table.table] = [];
                            }
                        });
                        this.$ls.set('relations',this.relationtables);
                });
            },
            created(){
                console.log('---on--created----');
                this.loginsys = this.$ls.get('loginsys')
                window.addEventListener('beforeunload', (event) => {
                    this.saverelation();
                })
            },
            beforeDestroy() {
                this.saverelation();
            }
        });
        </script>
        <?php             
    }

    
    /**
    *@noAuth
    *@url POST /genbytable
    *----------------------------------------------
    *FILE NAME:  CodegenController.php gen for Servit Framework Controller
    *Created by: Tlen<limweb@hotmail.com>
    *DATE:  2020-03-10(Tue)  18:26:14 
    
    *----------------------------------------------
    */
    public function genbytable(){
        try {
            $input = $this->input->input->toArray();
            return [
                'input' => $input,
                'status' => '1',
                'success'=> true,
                'path' => __DIR__,
            ];
        } catch (Exception $e) {
            return[
                'status' => '0',
                'success'=> false,
                'msg'=> $e->getMessage(),
            ]; 
        }
    }
    
    
    /**
    *@noAuth
    *@url POST /genall
    *----------------------------------------------
    *FILE NAME:  CodegenController.php gen for Servit Framework Controller
    *Created by: Tlen<limweb@hotmail.com>
    *DATE:  2020-03-10(Tue)  18:26:46 
    
    *----------------------------------------------
    */
    public function genall(){
        try {
            $input = $this->input->input->toArray();
            $route = '';
            foreach($input['tabledata'] as $table){
                file_put_contents(__DIR__.'/../'.$table['controller']['filename'],$table['controller']['doc']);
                file_put_contents(__DIR__.'/../../models/'.$table['model']['filename'],$table['model']['doc']);
                file_put_contents(__DIR__.'/../../services/'.$table['service']['filename'],$table['service']['doc']);
                $route .= $table['routebygen']['doc'];
                $sqls = $table['sql']['doc'];
                // dump($sql);
                if($sqls){
                    $sqlarrs = explode(';',$sqls);
                    foreach($sqlarrs as $sql){
                        if($sql){
                            Capsule::statement($sql);
                        }
                    }
                }
            }

            $loginroute = "";
            if($input['logindata']['model']){
                file_put_contents(__DIR__.'/../../models/Login.php',$input['logindata']['model']);
            }
            if($input['logindata']['jwtsrv']){
                file_put_contents(__DIR__.'/../../services/JwtService.php',$input['logindata']['jwtsrv']);
            }
            if($input['logindata']['loginsrv']){
                file_put_contents(__DIR__.'/../../services/LoginService.php',$input['logindata']['loginsrv']);
            }

            if($input['logindata']['jwtctl']){
                file_put_contents(__DIR__.'/../JwtController.php',$input['logindata']['jwtctl']);
                $loginroute .= '$server->addClass("JwtController","",""); '."\n";
            }

            if($input['logindata']['loginctl']){
                file_put_contents(__DIR__.'/../LoginController.php',$input['logindata']['loginctl']);
                $loginroute .= '$server->addClass("LoginController","",""); '."\n";
            }
            if($input['logindata']['vuehtml']){
                file_put_contents(__DIR__.'/../../views/pages/Login.vue',$input['logindata']['vuehtml']);
            }


            $route = '<?php //----route---generate by vuevm-----'."\n".$loginroute.$route;
            file_put_contents(__DIR__.'/../../route/routebygen.php',$route);
            return [
                'input' => $input,
                'status' => '1',
                'success'=> true,
                'path' => __DIR__,
            ];
        } catch (Exception $e) {
            return[
                'status' => '0',
                'success'=> false,
                'msg'=> $e->getMessage(),
            ]; 
        }
    }

    
    /**
    *@noAuth
    *@url GET /test
    *----------------------------------------------
    *FILE NAME:  CodegenController.php gen for Servit Framework Controller
    *Created by: Tlen<limweb@hotmail.com>
    *DATE:  2020-03-11(Wed)  22:05:28 
    
    *----------------------------------------------
    */
    public function test(){
        dump($this);
    }
    
    
}