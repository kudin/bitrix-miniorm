bitrix-miniorm
==============
		<p>Использовал этот класс для доступа к своим таблицам в своих модулях. Но сейчас с выходом 14го битрикса этот класс уже неактуален..</p>
<p>Для работы с своими таблицами мы создаём класс-наследник моего абстрактного класса tablestpl: </p>
<code>class kupons extends ktablestpl { 
       var $tablename = "kupons"; 
       var $fields = array( 
       "ID"   =>  array( "TYPE" => "INT" ), 
       "USER_ID"   =>  array( "TYPE" => "INT" ), 
       "CODE"   =>  array( "TYPE" => "VARCHAR" ), 
       "SKIDKA"   =>  array( "TYPE" => "INT" ), 
   ); 
};
</code>

<P>в $tablename мы указываем имя таблицы (Необязательно такое же как имя класса), а в массиве $fields описываем поля таблицы и их тип</P>

<P>Всё. теперь наш класс наследует следующие методы для работы с таблицой: </P>
<ul><li>Add($arr)</li><li>GetList($order, $filter, $select, $group)</li><li>Update($id, $arr)</li><li>RemoveAll()</li><li>RemoveByID($id)</li>
<li>GetByID($id)</li><li>GetAll()</li>
</ul>
<P>Теперь с таблицой можно работать так:</P>
<P>Добавление:</P>
<code>$kupons = new kupons();
$kupons->Add(array('CODE'=>'test123', 'SKIDKA'=>10));
//+----+---------+---------+--------+
//| ID | USER_ID | CODE    | SKIDKA |
//+----+---------+---------+--------+
//|  1 |       0 | test123 |     10 |
//+----+---------+---------+--------+
</code>

<P>Удаление:</P>
<code>$kupons->RemoveByID(2);</code>

<P>Выборка:</P><P>На примере заполненной базы покажу какие запросы в неё составляются этим классом:</P>
<code>пример таблицы: 
+----+---------+---------+--------+
| ID | USER_ID | CODE    | SKIDKA |
+----+---------+---------+--------+
|  1 |       0 | test123 |     10 |
|  2 |       5 | test    |     22 |
|  3 |       5 | aaaaa   |     50 |
|  4 |       5 | bbbbb   |     50 |
|  5 |       6 | test    |     15 |
|  6 |       7 | test    |     19 |
|  7 |       7 |         |      0 |
+----+---------+---------+--------+</code>

<textarea class='kudincode' readonly>$kupons = new kupons();
$res = $kupons->GetList(array(), array('CODE'=>'test', '<SKIDKA'=>20));
while($row = $res->Fetch()){	
	var_dump($row);
} 

// формирует нормальный запрос:
// SELECT * FROM `kupons` WHERE (`CODE` = "test") 
        AND (`SKIDKA` < "20") ORDER BY `ID` ASC ;
 
// результат: 
array(4) {
  ["ID"]=>
  string(1) "5"
  ["USER_ID"]=>
  string(1) "6"
  ["CODE"]=>
  string(4) "test"
  ["SKIDKA"]=>
  string(2) "15"
}

array(4) {
  ["ID"]=>
  string(1) "6"
  ["USER_ID"]=>
  string(1) "7"
  ["CODE"]=>
  string(4) "test"
  ["SKIDKA"]=>
  string(2) "19"
} </textarea>
<textarea class='kudincode' readonly>$kupons = new kupons();
$res = $kupons->GetList(array('SKIDKA'=>'ASC','ID'=>'DESC'), 
   array('USER_ID'=>array(5,6,9,10), 
           '?CODE'=>array('test','%bb%'), 
            '>SKIDKA'=>10,
           '!SKIDKA'=>22),
   array('ID', 'CODE', 'SKIDKA'));

while($row = $res->Fetch()){
	var_dump($row);
} 

// формирует запрос:
SELECT ID, CODE, SKIDKA FROM `kupons`
 WHERE (`USER_ID` IN ( "5","6","9","10")) 
AND ((`CODE` LIKE "test") 
 OR (`CODE` LIKE "%bb%")) 
AND (`SKIDKA` > "10") AND (`SKIDKA` != "22")
 ORDER BY `SKIDKA` ASC, `ID` DESC ;
 
// результат: 
array(3) {
  ["ID"]=>
  string(1) "5"
  ["CODE"]=>
  string(4) "test"
  ["SKIDKA"]=>
  string(2) "15"
}

array(3) {
  ["ID"]=>
  string(1) "4"
  ["CODE"]=>
  string(5) "bbbbb"
  ["SKIDKA"]=>
  string(2) "50"
}</textarea>
