<?php
namespace App\Collections;

use App\Exceptions\WithdrawException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class JavaBanksCollection extends Collection
{

    public function get()
    {
        return $this->banks;
    }

    private $banks = [
        ['id'=>0,  'code'=>'',          'is_bank'=>true,  'name'=>'allbank'],
        ['id'=>1,  'code'=>'CNBK004',   'is_bank'=>true,  'name'=>'中国工商银行'],
        ['id'=>2,  'code'=>'CNBK005',   'is_bank'=>true,  'name'=>'上海农村商业银行'],
        ['id'=>3,  'code'=>'CNBK006',   'is_bank'=>true,  'name'=>'杭州银行'],
        ['id'=>4,  'code'=>'CNBK007',   'is_bank'=>true,  'name'=>'河北银行'],
        ['id'=>5,  'code'=>'CNBK008',   'is_bank'=>true,  'name'=>'深圳发展银行'],
        ['id'=>6,  'code'=>'CNBK009',   'is_bank'=>true,  'name'=>'浙江泰隆商业银行'],
        ['id'=>7,  'code'=>'CNBK010',   'is_bank'=>true,  'name'=>'浙商银行'],
        ['id'=>8,  'code'=>'CNBK011',   'is_bank'=>true,  'name'=>'中国民生银行'],
        ['id'=>9,  'code'=>'CNBK012',   'is_bank'=>true,  'name'=>'中国光大银行'],
        ['id'=>10, 'code'=>'CNBK013',   'is_bank'=>true,  'name'=>'中国农业银行'],
        ['id'=>11, 'code'=>'CNBK014',   'is_bank'=>true,  'name'=>'中国邮政储蓄'],
        ['id'=>12, 'code'=>'CNBK015',   'is_bank'=>true,  'name'=>'中国建设银行'],
        ['id'=>13, 'code'=>'CNBK016',   'is_bank'=>true,  'name'=>'中国银行'],
        ['id'=>14, 'code'=>'CNBK017',   'is_bank'=>true,  'name'=>'中信银行'],
        ['id'=>15, 'code'=>'CNBK018',   'is_bank'=>true,  'name'=>'东亚银行'],
        ['id'=>16, 'code'=>'CNBK019',   'is_bank'=>true,  'name'=>'北京农村商业银行'],
        ['id'=>17, 'code'=>'CNBK020',   'is_bank'=>true,  'name'=>'北京银行'],
        ['id'=>18, 'code'=>'CNBK021',   'is_bank'=>true,  'name'=>'宁波银行'],
        ['id'=>19, 'code'=>'CNBK022',   'is_bank'=>true,  'name'=>'平安银行'],
        ['id'=>20, 'code'=>'CNBK023',   'is_bank'=>true,  'name'=>'交通银行'],
        ['id'=>21, 'code'=>'CNBK024',   'is_bank'=>true,  'name'=>'兴业银行'],
        ['id'=>22, 'code'=>'CNBK025',   'is_bank'=>true,  'name'=>'华夏银行'],
        ['id'=>23, 'code'=>'CNBK026',   'is_bank'=>true,  'name'=>'成都银行'],
        ['id'=>24, 'code'=>'CNBK027',   'is_bank'=>true,  'name'=>'招商银行'],
        ['id'=>25, 'code'=>'CNBK028',   'is_bank'=>true,  'name'=>'南京银行'],
        ['id'=>26, 'code'=>'CNBK029',   'is_bank'=>true,  'name'=>'海口联合农商行'],
        ['id'=>27, 'code'=>'CNBK030',   'is_bank'=>true,  'name'=>'渤海银行'],
        ['id'=>28, 'code'=>'CNBK031',   'is_bank'=>true,  'name'=>'广州农商银行'],
        ['id'=>29, 'code'=>'CNBK032',   'is_bank'=>true,  'name'=>'农村信用社'],
        ['id'=>30, 'code'=>'CNBK033',   'is_bank'=>true,  'name'=>'厦门银行'],
        ['id'=>31, 'code'=>'CNBK034',   'is_bank'=>true,  'name'=>'桂林银行'],
        ['id'=>32, 'code'=>'CNBK0999',  'is_bank'=>true,  'name'=>'其他银行'],
        ['id'=>33, 'code'=>'CNBK001',   'is_bank'=>true,  'name'=>'上海浦东发展银行'],
        ['id'=>34, 'code'=>'CNBK002',   'is_bank'=>true,  'name'=>'上海银行'],
        ['id'=>35, 'code'=>'CNBK003',   'is_bank'=>true,  'name'=>'广东发展银行'],
        ['id'=>36, 'code'=>'CNBK0998',  'is_bank'=>false, 'name'=>'支付宝'],
        ['id'=>41, 'code'=>'',          'is_bank'=>true,  'name'=>'天津银行'],
        ['id'=>42, 'code'=>'',          'is_bank'=>true,  'name'=>'浙江稠州商业银行'],
        ['id'=>43, 'code'=>'',          'is_bank'=>true,  'name'=>'广州银行'],
        ['id'=>44, 'code'=>'',          'is_bank'=>true,  'name'=>'顺德农商银行'],
        ['id'=>45, 'code'=>'',          'is_bank'=>true,  'name'=>'东莞银行'],
        ['id'=>46, 'code'=>'',          'is_bank'=>true,  'name'=>'长沙银行'],
        ['id'=>47, 'code'=>'',          'is_bank'=>true,  'name'=>'温州银行'],
        ['id'=>48, 'code'=>'',          'is_bank'=>true,  'name'=>'重庆银行'],
        ['id'=>49, 'code'=>'',          'is_bank'=>true,  'name'=>'江苏银行'],
        ['id'=>50, 'code'=>'',          'is_bank'=>true,  'name'=>'汉口银行'],
        ['id'=>51, 'code'=>'',          'is_bank'=>true,  'name'=>'晋商银行'],
        ['id'=>52, 'code'=>'',          'is_bank'=>true,  'name'=>'珠海农村信用合作联社'],
        ['id'=>53, 'code'=>'',          'is_bank'=>true,  'name'=>'尧都区农村信用联社'],
        ['id'=>54, 'code'=>'',          'is_bank'=>true,  'name'=>'广西农村信用社'],
        ['id'=>55, 'code'=>'',          'is_bank'=>true,  'name'=>'吉林银行'],
        ['id'=>56, 'code'=>'',          'is_bank'=>true,  'name'=>'内蒙古银行'],
        ['id'=>57, 'code'=>'',          'is_bank'=>false, 'name'=>'微信'],
        ['id'=>58, 'code'=>'',          'is_bank'=>true,  'name'=>'泰隆银行'],
        ['id'=>59, 'code'=>'',          'is_bank'=>true,  'name'=>'徽商银行'],
        ['id'=>60, 'code'=>'',          'is_bank'=>true,  'name'=>'徽商银行'],
        ['id'=>61, 'code'=>'',          'is_bank'=>true,  'name'=>'广州市商业银行'],
        ['id'=>62, 'code'=>'',          'is_bank'=>true,  'name'=>'包商银行'],
        ['id'=>63, 'code'=>'',          'is_bank'=>true,  'name'=>'韩亚银行'],
        ['id'=>64, 'code'=>'',          'is_bank'=>true,  'name'=>'天津农商银行'],
        ['id'=>65, 'code'=>'',          'is_bank'=>true,  'name'=>'外换银行'],
        ['id'=>66, 'code'=>'',          'is_bank'=>true,  'name'=>'新韩银行'],
        ['id'=>67, 'code'=>'',          'is_bank'=>true,  'name'=>'深圳农商行'],
        ['id'=>68, 'code'=>'',          'is_bank'=>true,  'name'=>'东莞农商行'],
        ['id'=>69, 'code'=>'',          'is_bank'=>true,  'name'=>'安徽农金'],
        ['id'=>70, 'code'=>'',          'is_bank'=>true,  'name'=>'广西北部湾银行'],
        ['id'=>71, 'code'=>'',          'is_bank'=>true,  'name'=>'昆山农村商业银行'],
        ['id'=>72, 'code'=>'',          'is_bank'=>true,  'name'=>'苏州银行'],
        ['id'=>73, 'code'=>'',          'is_bank'=>true,  'name'=>'张家港农商银行'],
        ['id'=>74, 'code'=>'',          'is_bank'=>true,  'name'=>'南昌银行'],
        ['id'=>75, 'code'=>'',          'is_bank'=>true,  'name'=>'上饶银行'],
        ['id'=>76, 'code'=>'',          'is_bank'=>true,  'name'=>'东营银行'],
        ['id'=>77, 'code'=>'',          'is_bank'=>true,  'name'=>'莱商银行'],
        ['id'=>78, 'code'=>'',          'is_bank'=>true,  'name'=>'临商银行'],
        ['id'=>79, 'code'=>'',          'is_bank'=>true,  'name'=>'齐商银行'],
        ['id'=>80, 'code'=>'',          'is_bank'=>true,  'name'=>'青岛银行'],
        ['id'=>81, 'code'=>'',          'is_bank'=>true,  'name'=>'日照银行'],
        ['id'=>82, 'code'=>'',          'is_bank'=>true,  'name'=>'泰安银行'],
        ['id'=>83, 'code'=>'',          'is_bank'=>true,  'name'=>'威海商行'],
        ['id'=>84, 'code'=>'',          'is_bank'=>true,  'name'=>'烟台银行'],
        ['id'=>85, 'code'=>'',          'is_bank'=>true,  'name'=>'大连银行'],
        ['id'=>86, 'code'=>'',          'is_bank'=>true,  'name'=>'锦州银行'],
        ['id'=>87, 'code'=>'',          'is_bank'=>true,  'name'=>'鞍山银行'],
        ['id'=>88, 'code'=>'',          'is_bank'=>true,  'name'=>'葫芦岛银行'],
        ['id'=>89, 'code'=>'',          'is_bank'=>true,  'name'=>'湖州银行'],
        ['id'=>90, 'code'=>'',          'is_bank'=>true,  'name'=>'鄞州银行'],
        ['id'=>91, 'code'=>'',          'is_bank'=>true,  'name'=>'黄河农村商业银行'],
        ['id'=>92, 'code'=>'',          'is_bank'=>true,  'name'=>'九江银行'],
        ['id'=>93, 'code'=>'',          'is_bank'=>true,  'name'=>'上海交通银行'],
        ['id'=>94, 'code'=>'',          'is_bank'=>true,  'name'=>'兰州银行'],
        ['id'=>95, 'code'=>'',          'is_bank'=>true,  'name'=>'台州银行'],
        ['id'=>96, 'code'=>'',          'is_bank'=>true,  'name'=>'珠海农村商业银行'],
        ['id'=>97, 'code'=>'',          'is_bank'=>true,  'name'=>'尧都农村商业银行'],
        ['id'=>98, 'code'=>'',          'is_bank'=>true,  'name'=>'南洋商业银行'],
        ['id'=>99, 'code'=>'',          'is_bank'=>true,  'name'=>'恒丰银行'],
        ['id'=>100, 'code'=>'',         'is_bank'=>true,  'name'=>'富邦'],
        ['id'=>101, 'code'=>'',         'is_bank'=>true,  'name'=>'济宁银行'],
        ['id'=>102, 'code'=>'',         'is_bank'=>true,  'name'=>'嘉兴银行清算中心'],
        ['id'=>103, 'code'=>'',         'is_bank'=>true,  'name'=>'西安银行'],
        ['id'=>104, 'code'=>'',         'is_bank'=>true,  'name'=>'江苏江阴农村商业银行'],
        ['id'=>105, 'code'=>'',         'is_bank'=>true,  'name'=>'无锡农村商业银行'],
        ['id'=>106, 'code'=>'',         'is_bank'=>true,  'name'=>'贵州省农村信用社联合社'],
        ['id'=>107, 'code'=>'',         'is_bank'=>true,  'name'=>'盛京银行'],
        ['id'=>108, 'code'=>'',         'is_bank'=>true,  'name'=>'天津滨海农村商业银行'],
        ['id'=>109, 'code'=>'',         'is_bank'=>true,  'name'=>'中德住房储蓄银行'],
        ['id'=>110, 'code'=>'',         'is_bank'=>true,  'name'=>'宜宾市商业银行'],
        ['id'=>111, 'code'=>'',         'is_bank'=>true,  'name'=>'丹东银行'],
        ['id'=>112, 'code'=>'',         'is_bank'=>true,  'name'=>'重庆富民银行'],
        ['id'=>113, 'code'=>'',         'is_bank'=>true,  'name'=>'朝阳银行'],
        ['id'=>114, 'code'=>'',         'is_bank'=>true,  'name'=>'四川新网银行'],
        ['id'=>115, 'code'=>'',         'is_bank'=>true,  'name'=>'湖北银行'],
        ['id'=>116, 'code'=>'',         'is_bank'=>true,  'name'=>'广东南粤银行'],
        ['id'=>117, 'code'=>'',         'is_bank'=>true,  'name'=>'中原银行'],
        ['id'=>118, 'code'=>'',         'is_bank'=>true,  'name'=>'湖北银行'],
        ['id'=>119, 'code'=>'',         'is_bank'=>true,  'name'=>'龙江银行'],
        ['id'=>120, 'code'=>'',         'is_bank'=>true,  'name'=>'企业银行'],
        ['id'=>121, 'code'=>'',         'is_bank'=>true,  'name'=>'赣州银行'],
        ['id'=>122, 'code'=>'',         'is_bank'=>true,  'name'=>'深圳前海微众银行'],
        ['id'=>123, 'code'=>'',         'is_bank'=>true,  'name'=>'营口银行'],
        ['id'=>124, 'code'=>'',         'is_bank'=>true,  'name'=>'晋城银行'],
        ['id'=>125, 'code'=>'',         'is_bank'=>true,  'name'=>'友利银行'],
        ['id'=>126, 'code'=>'',         'is_bank'=>true,  'name'=>'太仓农村商业银行'],
        ['id'=>127, 'code'=>'',         'is_bank'=>true,  'name'=>'枣庄银行'],
        ['id'=>128, 'code'=>'',         'is_bank'=>true,  'name'=>'衡水银行'],
        ['id'=>129, 'code'=>'',         'is_bank'=>true,  'name'=>'金华银行'],
        ['id'=>130, 'code'=>'',         'is_bank'=>true,  'name'=>'广东省农村信用社联合社'],
        ['id'=>131, 'code'=>'',         'is_bank'=>true,  'name'=>'江苏江南农村商业银行'],
        ['id'=>132, 'code'=>'',         'is_bank'=>true,  'name'=>'贵州银行'],
        ['id'=>133, 'code'=>'',         'is_bank'=>true,  'name'=>'海南银行'],
        ['id'=>134, 'code'=>'',         'is_bank'=>true,  'name'=>'乌海银行'],
        ['id'=>135, 'code'=>'',         'is_bank'=>true,  'name'=>'福建海峡银行'],
        ['id'=>136, 'code'=>'',         'is_bank'=>true,  'name'=>'贵阳银行'],
        ['id'=>137, 'code'=>'',         'is_bank'=>true,  'name'=>'邢台银行'],
        ['id'=>138, 'code'=>'',         'is_bank'=>true,  'name'=>'攀枝花市商业银行'],
        ['id'=>139, 'code'=>'',         'is_bank'=>true,  'name'=>'潍坊银行'],
        ['id'=>140, 'code'=>'',         'is_bank'=>true,  'name'=>'吴江农村商业银行'],
        ['id'=>141, 'code'=>'',         'is_bank'=>true,  'name'=>'云南省农村信用合作联社'],
        ['id'=>142, 'code'=>'',         'is_bank'=>true,  'name'=>'昆仑银行'],
        ['id'=>143, 'code'=>'',         'is_bank'=>true,  'name'=>'潍坊银行'],
        ['id'=>144, 'code'=>'',         'is_bank'=>true,  'name'=>'自贡银行'],
        ['id'=>145, 'code'=>'',         'is_bank'=>true,  'name'=>'广东华兴银行'],
        ['id'=>146, 'code'=>'',         'is_bank'=>true,  'name'=>'齐鲁银行'],
        ['id'=>147, 'code'=>'',         'is_bank'=>true,  'name'=>'江苏长江商业银行'],
        ['id'=>148, 'code'=>'',         'is_bank'=>true,  'name'=>'焦作中旅银行'],
        ['id'=>149, 'code'=>'',         'is_bank'=>true,  'name'=>'沧州银行'],
        ['id'=>150, 'code'=>'',         'is_bank'=>true,  'name'=>'郑州银行'],
        ['id'=>151, 'code'=>'',         'is_bank'=>true,  'name'=>'武汉农村商业银行'],
        ['id'=>152, 'code'=>'',         'is_bank'=>true,  'name'=>'绍兴银行'],
        ['id'=>153, 'code'=>'',         'is_bank'=>true,  'name'=>'德州银行'],
        ['id'=>154, 'code'=>'',         'is_bank'=>true,  'name'=>'宁夏银行'],
        ['id'=>155, 'code'=>'',         'is_bank'=>true,  'name'=>'青海银行'],
        ['id'=>156, 'code'=>'',         'is_bank'=>true,  'name'=>'浙江民泰商业银行'],
        ['id'=>157, 'code'=>'',         'is_bank'=>true,  'name'=>'乌鲁木齐银行'],
        ['id'=>158, 'code'=>'',         'is_bank'=>true,  'name'=>'富滇银行'],
        ['id'=>159, 'code'=>'',         'is_bank'=>true,  'name'=>'鄂尔多斯银行'],
        ['id'=>160, 'code'=>'',         'is_bank'=>true,  'name'=>'承德银行'],
        ['id'=>161, 'code'=>'',         'is_bank'=>true,  'name'=>'重庆农村商业银行'],
        ['id'=>162, 'code'=>'',         'is_bank'=>true,  'name'=>'洛阳银行'],
        ['id'=>163, 'code'=>'',         'is_bank'=>true,  'name'=>'柳州银行'],
        ['id'=>164, 'code'=>'',         'is_bank'=>true,  'name'=>'江苏常熟农村商业银行'],
        ['id'=>165, 'code'=>'',         'is_bank'=>true,  'name'=>'绵阳市商业银行'],
        ['id'=>166, 'code'=>'',         'is_bank'=>true,  'name'=>'泉州银行'],
        ['id'=>167, 'code'=>'',         'is_bank'=>true,  'name'=>'南充市商业银行'],
        ['id'=>168, 'code'=>'',         'is_bank'=>true,  'name'=>'四川省农村信用合作联社'],
        ['id'=>169, 'code'=>'',         'is_bank'=>true,  'name'=>'长安银行'],
        ['id'=>170, 'code'=>'',         'is_bank'=>true,  'name'=>'廊坊银行'],
        ['id'=>171, 'code'=>'',         'is_bank'=>true,  'name'=>'成都农商银行'],
        ['id'=>172, 'code'=>'',         'is_bank'=>true,  'name'=>'厦门国际银行'],
        ['id'=>173, 'code'=>'',         'is_bank'=>true,  'name'=>'曲靖市商业银行'],
        ['id'=>174, 'code'=>'',         'is_bank'=>true,  'name'=>'晋中银行'],
        ['id'=>175, 'code'=>'',         'is_bank'=>true,  'name'=>'华融湘江银行'],
        ['id'=>176, 'code'=>'',         'is_bank'=>true,  'name'=>'重庆三峡银行'],
        ['id'=>177, 'code'=>'',         'is_bank'=>true,  'name'=>'浙江网商银行'],
        ['id'=>178, 'code'=>'',         'is_bank'=>true,  'name'=>'花旗银行'],
        ['id'=>179, 'code'=>'',         'is_bank'=>true,  'name'=>'上海华瑞银行'],
        ['id'=>180, 'code'=>'',         'is_bank'=>true,  'name'=>'石嘴山银行'],
        ['id'=>181, 'code'=>'',         'is_bank'=>true,  'name'=>'辽阳银行'],
        ['id'=>182, 'code'=>'',         'is_bank'=>true,  'name'=>'甘肃银行'],
        ['id'=>183, 'code'=>'',         'is_bank'=>true,  'name'=>'营口沿海银行'],
        ['id'=>184, 'code'=>'',         'is_bank'=>true,  'name'=>'保定银行'],
        ['id'=>185, 'code'=>'',         'is_bank'=>true,  'name'=>'温州民商银行'],
        ['id'=>186, 'code'=>'',         'is_bank'=>true,  'name'=>'众邦银行'],
        ['id'=>187, 'code'=>'',         'is_bank'=>true,  'name'=>'渣打银行'],
        ['id'=>188, 'code'=>'',         'is_bank'=>true,  'name'=>'汇丰银行'],
        ['id'=>189, 'code'=>'',         'is_bank'=>true,  'name'=>'西藏银行'],
        ['id'=>190, 'code'=>'',         'is_bank'=>true,  'name'=>'大连农村商业银行'],
        ['id'=>191, 'code'=>'',         'is_bank'=>true,  'name'=>'恒生银行'],
        ['id'=>192, 'code'=>'',         'is_bank'=>true,  'name'=>'宁波东海银行'],
        ['id'=>193, 'code'=>'',         'is_bank'=>true,  'name'=>'长治银行'],
        ['id'=>194, 'code'=>'',         'is_bank'=>true,  'name'=>'凉山州商业银行'],
        ['id'=>195, 'code'=>'CNBK0996', 'is_bank'=>false, 'name'=>'CGP'],
        ['id'=>196, 'code'=>'CNBK0997', 'is_bank'=>false, 'name'=>'USDT'],
    ];



    public function __construct()
    {
        parent::__construct();

    }

    public function getIdByCode($code)
    {
        foreach ($this->banks as $value) {
            if ($value['code'] == $code) return $value['id'];
        }
        return null;
    }

    public function getIdByName($name)
    {
        foreach ($this->banks as $value) {
            if ($value['name'] == $name) return $value['id'];
        }
        return null;
    }

    public function checkBankExist($name)
    {
        foreach ($this->banks as $value) {
            if ($value['name'] == $name) return true;
        }
        return false;
    }

    public function getCodeById($id)
    {
        foreach ($this->banks as $value) {
            if ($value['id'] == $id) return $value['code'];
        }
        return null;
    }

    public function getNameById($id)
    {
        foreach ($this->banks as $value) {
            if ($value['id'] == $id) return $value['name'];
        }
        return null;
    }

    public function getNameByCode($code)
    {
        foreach ($this->banks as $value) {
            if ($value['code'] == $code) return $value['name'];
        }
        return null;
    }

    public function isBank($id)
    {
        foreach ($this->banks as $value) {
            if ($value['id'] == $id) return $value['is_bank'];
        }
        return null;
    }



}
