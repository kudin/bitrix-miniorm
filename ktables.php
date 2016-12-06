<?php 

abstract class ktablestpl{

    function Add($arr){
        global $DB;
        $insertArr['ID'] = 'NULL';
        foreach ($this->fields as $fieldname => $fieldArr){
            if($fieldname == 'ID')
                continue;
            if($arr[$fieldname]){
                $insertArr[$fieldname] = $DB->ForSql($arr[$fieldname]);
                $norm = true;
            } else {
                $insertArr[$fieldname] = '';
            }
        }

        if(!$norm)
            return false;
        
        $strSql = 'INSERT INTO `' . $this->tablename . '`
(`' . implode('` , `', array_keys($insertArr)) . '`)
VALUES
(\'' . implode("','", $insertArr) . '\');';
          
        $DB->Query($strSql);
        return intval($DB->LastID());
    }
    
    function GetList($order, $filter, $select, $group){
        global $DB;
        if (!$order)
            $order = array('ID' => 'ASC');
        foreach ($order as $k => $v) {
            $v = strtoupper($v) == 'ASC' ? $v : 'DESC';
            if (in_array($k, array_keys($this->fields)))
                $o[] = '`' . $DB->ForSql($k) . '` ' . $v;
        }
        $order = implode(', ', $o);
        foreach ($filter as $k => $v) {
            if (in_array($k, array_keys($this->fields))) {
                if (is_array($v)) {
                    $strTmp = '`' . $k . '` IN ( ';
                    foreach($filter[$k] as $key => $id){
                        $strTmp.= '"' . $DB->ForSql($id) . '"';
                        if($key != count($filter[$k]) - 1)
                            $strTmp.= ',';
                        }
                    $strTmp.= ')';
                    $f[] = $strTmp;
                } else {
                    $f[] = '`' . $k . '` = "' . $DB->ForSql($v) . '"';
                }
            } else {
                if(in_array($firstSymbol = substr($k, 0, 1), array('?', '!', '>', '<')) &&
                   in_array($k_ = substr($k, 1), array_keys($this->fields))){
                    switch (true) {
                        case $firstSymbol == '?':
                                if (is_array($v)) {
                                    $filTmp = array();
                                    foreach ($filter[$k] as $id)
                                        $filTmp[] = '`' . $k_ . '` LIKE "' . $DB->ForSql($id) . '"';
                                    $f[] = '(' . implode(') OR (', $filTmp) . ')';
                                } else {
                                    $f[] = '`' . $k_ . '` LIKE "' . $DB->ForSql($v) . '"';
                                }
                            break;
                        case $firstSymbol == '!':
                                $f[] = '`' . $k_ . '` != "' . $DB->ForSql($v) . '"';
                            break;
                        case $firstSymbol == '>' || $firstSymbol == '<':
                                $f[] = '`' . $k_ . '` ' . $firstSymbol . ' "' . $DB->ForSql($v) . '"';
                            break;
                        default:
                            break;
                    }
                }
                
            }
        }

        if(!$f)
            $where = '1';
        elseif(count($f) == 1)
            $where = "( {$f[0]} )";
        elseif(count($f) > 1)
            $where = '(' . implode(') AND (', $f) . ')';
        
        if(!$select){
            $select = '*';
        } else {
            if(!in_array('ID', $select))
                $select[] = 'ID';
            $select = implode(', ', $select);
        }
        
        if(!$group){
            $group = '';
        } else {
            $group = 'GROUP BY ' . implode(', ', $group);
            $order = false;
        }
        
        $strSql = 'SELECT ' . $select . ' FROM `' . $this->tablename . '`
' . ($where ? ' WHERE ' . $where : '') . '
' . ($order ? ' ORDER BY ' . $order : '') . ' ' . $group . ';';
            
        $rs = $DB->Query($strSql);
        return $rs;
    }
 
    public function Update($id, $arr) {
        $id = intval($id);
        if (!$id || !$arr || !is_array($arr))
            return false;
        global $DB;
        $f = array();
        foreach ($arr as $k => $v)
            if (in_array($k, array_keys($this->fields)))
                $f[] = '`' . $k . '` = "' . $DB->ForSql($v) . '" ';
        if (!count($f))
            return false;
        $result = $DB->Query(' UPDATE `'. $this->tablename .
                             '` SET '. implode(',', $f) .
                             ' WHERE `ID` = '. $id .';');
        return $result;
    }
    
    function RemoveAll() {
        global $DB;
        $DB->Query("DELETE FROM `" . $this->tablename . "`");
    }
     
    function RemoveByID($id) {
        $id = intval($id);
        if ($id <= 0)
            return false;
        global $DB;
        $DB->Query("DELETE FROM `" . $this->tablename . "` WHERE `ID` = {$id}");
    }
    
    function GetByID($id){
        return $this->GetList(array(), array('ID' => $id));
    }
     
    function GetAll($arOrder = array("ID"=>"DESC")){
        return $this->GetList($arOrder, array());
    }
     
   function CreateTable() {
        $query = "CREATE TABLE IF NOT EXISTS `" . $this->tablename . "` \n (`ID` INT(11) NOT NULL AUTO_INCREMENT, \n";
        foreach ($this->fields as $fieldName => $fieldProps) {
            if($fieldName == 'ID') {
                continue;
            }
            if(!$fieldProps['MAX_SIZE']) { 
                switch ($fieldProps['TYPE']) {
                    case 'VARCHAR':
                        $fieldProps['MAX_SIZE'] = 255;
                        break; 
                    case 'INT': 
                        $fieldProps['MAX_SIZE'] = 4;
                        break;  
                    case 'DATETIME': 
                        $fieldProps['MAX_SIZE'] = false;
                        break;  
                }
            }
            $query = $query . " `" . $fieldName . "` " . $fieldProps['TYPE'];
            if($fieldProps['MAX_SIZE']) {
                $query = $query . "(" . $fieldProps['MAX_SIZE'] . ")" ; 
            }
            $query = $query . ", \n";
        }
        $query = $query . " PRIMARY KEY(`ID`) );"; 
        global $DB;
        $DB->Query($query);
    }
    
}
