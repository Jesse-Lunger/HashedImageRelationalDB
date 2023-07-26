<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('connectionData.txt');

$conn = mysqli_connect($server, $user, $pass, $dbname, $port)
or die('Error connecting to MySQL server.');

if (isset($_POST['item1'])) {
  $item1 = mysqli_real_escape_string($conn, $_POST['item1']);

  $query = "SELECT DISTINCT a.area_name, s.name_of_shop AS supplier
            FROM area_of_town a 
            JOIN buildings b USING(area_of_town)
            JOIN shops s USING(building_id)
            JOIN items_in_shops iis USING(building_id)
            JOIN items i USING(item_id)
            WHERE i.item_name = '" . $item1 . "'
            UNION
            SELECT DISTINCT a.area_name, CONCAT(n.fname, ' ', n.lname) AS supplier
            FROM npcs n
            JOIN wandering_merchant wm USING(npc_id)
            JOIN area_of_town a USING(area_of_town)
            JOIN wm_items wi USING (npc_id)
            JOIN items i USING(item_id) 
            WHERE i.item_name = '" . $item1 . "'";
  print($query);
  $result = mysqli_query($conn, $query) or die(mysqli_error($conn));

  print "<pre>";
  print("\nLocations of requested item:");
  while($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
    print "\n";
    print "$row[area_name]  $row[supplier]";
  }
  print "</pre>";
  mysqli_free_result($result);

} else if (isset($_POST['item2'])) {
  $item2 = mysqli_real_escape_string($conn, $_POST['item2']);

  // delete current list
  $sql = "delete from wm_items where  item_id < 21;";
  $result = $conn->query($sql);
  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $sql = "UPDATE wandering_merchant
          SET area_of_town = floor(rand() * 3) + 1
          WHERE npc_id = 12;";
  $result = $conn->query($sql);
  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $sql = "UPDATE wandering_merchant
          SET area_of_town = floor(rand() * 3) + 1
          WHERE npc_id = 13;";
  $result = $conn->query($sql);
  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }

  //generate new list
  for ($i = 0; $i < 19; $i++) {
    // generate random npc_id between 12 and 13
    $npc_id = rand(12, 13);
    
    // execute SQL statement
    $sql = "INSERT INTO wm_items (item_id, quantity_of_item, npc_id)
            SELECT x.item_id, FLOOR(RAND() * 10) + 1, $npc_id
            FROM (SELECT FLOOR(RAND() * 20) + 1 AS item_id) AS x
            WHERE NOT EXISTS (
                SELECT 1 FROM wm_items WHERE item_id = x.item_id
            )
            LIMIT 10;";
    $result = $conn->query($sql);
    // check if query was successful
    if (!$result) {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
  }
  $query = "select n.fname, n.lname, i.item_name, wmi.quantity_of_item
            from npcs n
            join wandering_merchant wm using(npc_id)
            join wm_items wmi using(npc_id)
            join items i using(item_id)
            order by 2, 3;";
  $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
  print($query);
  print "<pre>";
  print("\nNet items wandering merchants now carry:");
  while($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
    print "\n";
    print "$row[fname] $row[lname] $row[item_name] $row[quantity_of_item]";
  }
  print "</pre>";

  mysqli_free_result($result);
} else if (isset($_POST['item3'])) {
  $item3 = mysqli_real_escape_string($conn, $_POST['item3']);
  if ($item3 == "shop_keepers"){
    $query = "select n.fname, n.lname, a.area_name, s.name_of_shop
              from npcs n 
              join shop_keepers snf using(npc_id)
              join homes h on(snf.home_building_id = h.building_id)
              join buildings b using(building_id)
              join area_of_town a using(area_of_town)
              join shops s using(building_id);";
  }
  else if ($item3 == "wandering_merchant"){
    $query =   "select n.fname, n.lname, a.area_name
                from npcs n join wandering_merchant snf using(npc_id) 
                join area_of_town a using(area_of_town);";
  }
  else{
    $query =  "select n.fname, n.lname, a.area_name, h.name_of_home
              from npcs n 
              join $item3 snf using(npc_id)
              join area_of_town a using(area_of_town)
              join homes h on(snf.home_building_id = h.building_id)
              join buildings b using(building_id);";
  }
  print($query);
  $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
  print($query);
  print "<pre>";
  print("\ninformation by class:");
  while($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
    print "\n";
    printf("%s %s %s %s", $row['fname'], $row['lname'], $row['area_name'], isset($row['name_of_home']) ? $row['name_of_home'] : '');
  }
  print "</pre>";

  mysqli_free_result($result);

} else if (isset($_POST['item4'])) {
  $item4 = mysqli_real_escape_string($conn, $_POST['item4']);
  $query = "SELECT COUNT(h.name_of_home) AS available_houses
  FROM homes h
  JOIN buildings b USING(building_id)
  WHERE NOT EXISTS (
      SELECT 1
      FROM npcs n
      LEFT JOIN $item4 t USING(npc_id)
      LEFT JOIN homes h2 ON (h2.building_id = t.home_building_id)
      WHERE h2.building_id = h.building_id 
  )
  AND h.name_of_home like '$item4%';";
  print($query);
  $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
  print($query);
  print "<pre>";
  print("\nAvailable number of homes for you");
  while($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
    print "\n";
    print "$row[available_houses]";
  }
  print "</pre>";

  mysqli_free_result($result);

} else if (isset($_POST['item5'])) {
  // For this part I used chatgpt as I'm not very familar with php and I 
  // Havent seen code that generates new keys before in sql, both of which
  // I wanted to learn.
  if(isset($_POST['item5']['fname']) && isset($_POST['item5']['lname']) && $_POST['item5']['fname'] !== '' && $_POST['item5']['lname'] !== '') {
    // Both first name and last name are set and not empty
    $fname = $_POST['item5']['fname'];
    $lname = $_POST['item5']['lname'];
  
    // Rest of your code here...
  } else {
    // Either first name or last name is not set or empty
    trigger_error('First and last name must not be blank', E_USER_ERROR);
  }
    // $item5 = mysqli_real_escape_string($conn, $_POST['item5']);
  $sql = "SELECT COALESCE(MIN(npc_id) + 1, 1) AS new_key
  FROM (
      SELECT 0 AS npc_id
      UNION ALL
      SELECT npc_id FROM npcs
  ) t1
  WHERE NOT EXISTS (
      SELECT 1 FROM npcs t2 WHERE t2.npc_id = t1.npc_id + 1
  )";
  $result = $conn->query($sql);
  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
  } else {
    $row = $result->fetch_assoc();
    $new_key = $row['new_key'];
  }
  $sql = "INSERT INTO npcs (npc_id, fname, lname)
  VALUES ($new_key, '$fname', '$lname');";
  $result = $conn->query($sql);
  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $sql = "INSERT INTO townsfolk (npc_id, area_of_town, home_building_id)
  VALUES ($new_key, ceil(rand() * 2) + 1 ,ceil(rand() * 6) + 6);";
  $result = $conn->query($sql);
  if (!$result) {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }

  print "<pre>";
  print("\n$fname $lname successfully joined!");
  print "</pre>";

} else if (isset($_POST['item6'])) {
  $sql = "SELECT COUNT(*) as count FROM townsfolk";
  $result = $conn->query($sql);
  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $count = $row['count'];
  } else {
      trigger_error('All townsfolk have been slain!', E_USER_ERROR);
    }
  $npc_id = 6 + $count;
  $sql = "SELECT fname, lname 
          FROM npcs
          where npc_id = $npc_id";
  $result = $conn->query($sql);
  $row = $result->fetch_assoc();
  $fname = $row['fname']; $lname = $row['lname'];
  $npc_id = 6 + $count;

  $sql = "delete from townsfolk where npc_id = $npc_id";
  $result = $conn->query($sql);
  if (!$result) {
      trigger_error('ERROR deleting from townsfolk', E_USER_ERROR);
  }
  $sql = "delete from npcs where npc_id = $npc_id";
  $result = $conn->query($sql);
  if (!$result) {
      trigger_error('ERROR deleting from npcs', E_USER_ERROR);
  }
  print("\n$fname $lname has been slain!");
}



mysqli_close($conn);
?>

<p>
<hr>

<p>
<a href="operations.txt" >Contents</a>
of the PHP program that created this page. 	 
 
</body>
</html>
	  
