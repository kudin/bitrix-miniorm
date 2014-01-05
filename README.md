
<p>Использовал этот класс для доступа к своим таблицам в своих модулях. Но сейчас с выходом 14го битрикса этот класс уже неактуален..</p>
<p>Для работы с своими таблицами мы создаём класс-наследник моего абстрактного класса tablestpl: </p>
<pre>class kupons extends ktablestpl { 
       var $tablename = "kupons"; 
       var $fields = array( 
       "ID"   =&gt;  array( "TYPE" =&gt; "INT" ), 
       "USER_ID"   =&gt;  array( "TYPE" =&gt; "INT" ), 
       "CODE"   =&gt;  array( "TYPE" =&gt; "VARCHAR" ), 
       "SKIDKA"   =&gt;  array( "TYPE" =&gt; "INT" ), 
   ); 
};
</pre>

<p>в $tablename мы указываем имя таблицы (Необязательно такое же как имя класса), а в массиве $fields описываем поля таблицы и их тип</p>

<p>Всё. теперь наш класс наследует следующие методы для работы с таблицой: </p>
<ul><li>Add($arr)</li><li>GetList($order, $filter, $select, $group)</li><li>Update($id, $arr)</li><li>RemoveAll()</li><li>RemoveByID($id)</li>
<li>GetByID($id)</li><li>GetAll()</li>
</ul>
<p>Теперь с таблицой можно работать так:</p>
<p>Добавление:</p>
<pre>$kupons = new kupons();
$kupons-&gt;Add(array('CODE'=&gt;'test123', 'SKIDKA'=&gt;10));
//+----+---------+---------+--------+
//| ID | USER_ID | CODE    | SKIDKA |
//+----+---------+---------+--------+
//|  1 |       0 | test123 |     10 |
//+----+---------+---------+--------+
</pre>

<p>Удаление:</p>
<pre>$kupons-&gt;RemoveByID(2);</pre>

<p>Выборка:</p><p>На примере заполненной базы покажу какие запросы в неё составляются этим классом:</p>
<pre>пример таблицы: 
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
+----+---------+---------+--------+</pre>

<pre>$kupons = new kupons();
$res = $kupons-&gt;GetList(array(), array('CODE'=&gt;'test', '&lt;SKIDKA'=&gt;20));
while($row = $res-&gt;Fetch()){	
	var_dump($row);
} 

// формирует нормальный запрос:
// SELECT * FROM `kupons` WHERE (`CODE` = "test") 
        AND (`SKIDKA` &lt; "20") ORDER BY `ID` ASC ;
 
// результат: 
array(4) {
  ["ID"]=&gt;
  string(1) "5"
  ["USER_ID"]=&gt;
  string(1) "6"
  ["CODE"]=&gt;
  string(4) "test"
  ["SKIDKA"]=&gt;
  string(2) "15"
}

array(4) {
  ["ID"]=&gt;
  string(1) "6"
  ["USER_ID"]=&gt;
  string(1) "7"
  ["CODE"]=&gt;
  string(4) "test"
  ["SKIDKA"]=&gt;
  string(2) "19"
} </textarea>
<textarea readonly="" class="kudincode">$kupons = new kupons();
$res = $kupons-&gt;GetList(array('SKIDKA'=&gt;'ASC','ID'=&gt;'DESC'), 
   array('USER_ID'=&gt;array(5,6,9,10), 
           '?CODE'=&gt;array('test','%bb%'), 
            '&gt;SKIDKA'=&gt;10,
           '!SKIDKA'=&gt;22),
   array('ID', 'CODE', 'SKIDKA'));

while($row = $res-&gt;Fetch()){
	var_dump($row);
} 

// формирует запрос:
SELECT ID, CODE, SKIDKA FROM `kupons`
 WHERE (`USER_ID` IN ( "5","6","9","10")) 
AND ((`CODE` LIKE "test") 
 OR (`CODE` LIKE "%bb%")) 
AND (`SKIDKA` &gt; "10") AND (`SKIDKA` != "22")
 ORDER BY `SKIDKA` ASC, `ID` DESC ;
 
// результат: 
array(3) {
  ["ID"]=&gt;
  string(1) "5"
  ["CODE"]=&gt;
  string(4) "test"
  ["SKIDKA"]=&gt;
  string(2) "15"
}

array(3) {
  ["ID"]=&gt;
  string(1) "4"
  ["CODE"]=&gt;
  string(5) "bbbbb"
  ["SKIDKA"]=&gt;
  string(2) "50"
}</pre>
