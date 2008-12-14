<?php
/*
Plugin Name: ABAgraph
Description: Graphing for Applied Behavioral Analysis for Autism. Requires php-image-graph, php-image-canvas, php-gd, php-pear, Image_Color. ABAgraph grew out of Simple Graph by Pasi Matilainen. 
Author: Ray Holland 
Version: 0.9.1 
Author URI: http://abacms.org/
*/ 

define('ABAGRAPH_PLUGIN_PATH', ABSPATH . '/wp-content/plugins/' .
	dirname(plugin_basename(__FILE__)));

define('ABAGRAPH_PLUGIN_URL', get_bloginfo('wpurl') . '/wp-content/plugins/'
	. dirname(plugin_basename(__FILE__)));

$abagraph_version		= "0.9";
$abagraph_db_version	= "0.9";


function draw_abagraph($table_prefix, $current_user,$table_id,$table_name){
include_once 'Image/Graph.php';     
global $wpdb;
$Graph =& Image_Graph::factory('graph', array(800,600)); 
$Graph->add(
    Image_Graph::vertical(
        Image_Graph::factory('title', array($current_user->first_name." $current_user->last_name:"."$table_name", 12)),        
        Image_Graph::vertical(
            $Plotarea = Image_Graph::factory('plotarea'),
            $Legend = Image_Graph::factory('legend'),
            90
        ),
       10 
    )
); 
$Legend->setPlotarea($Plotarea);
$activities=$wpdb->get_col("SELECT DISTINCT activity FROM ".$table_prefix."abagraph WHERE user_id={$current_user->data->ID} and table_id=$table_id ORDER by activity ASC");

$num_activities=sizeof($activities);
$colors=array('blue','red','fuchsia','lawngreen','purple');
  for($i=0;$i<=$num_activities-1;$i+=1){
$datings[$i]=$wpdb->get_results("SELECT stamp,value FROM ".$table_prefix."abagraph WHERE user_id={$current_user->data->ID} and activity='$activities[$i]' and table_id=$table_id ORDER by activity ASC, stamp ASC");
$Dataset[$i] =& Image_Graph::factory('dataset'); 

if ($activities[$i] == "Generalization"){
$Plot[$i] =& $Plotarea->addNew('Image_Graph_Plot_Dot', array(&$Dataset[$i]));
$Marker1 =& Image_Graph::factory('Image_Graph_Marker_Triangle');
$Marker1->setFillColor('green');
$Marker1->setLineColor('black');
$Plot[$i]->setMarker($Marker1);
$Plot[$i]->setTitle($activities[$i]); 
}
else if ($activities[$i] == "Interobserver Agreement"){
$Plot[$i] =& $Plotarea->addNew('Image_Graph_Plot_Dot', array(&$Dataset[$i]));
$Marker2 =& Image_Graph::factory('Image_Graph_Marker_Plus');
$Marker2->setFillColor('yellow');
$Marker2->setLineColor('black');
$Plot[$i]->setMarker($Marker2);
$Plot[$i]->setTitle($activities[$i]); 
}
else if ($activities[$i] == "Baseline"){
$Plot[$i] =& $Plotarea->addNew('Plot_Impulse', array(&$Dataset[$i]));
$Plot[$i]->setLineColor('gold');
$Plot[$i]->setTitle($activities[$i]); 
}
else if ($activities[$i] == "Condition Line"){
$Plot[$i] =& $Plotarea->addNew('Plot_Impulse', array(&$Dataset[$i]));
$Plot[$i]->setLineColor('brown');
$Plot[$i]->setTitle($activities[$i]); 
}
else if ($activities[$i] == "Criteria Line"){
$Plot[$i] =& $Plotarea->addNew('line', array(&$Dataset[$i]));
$Plot[$i]->setLineColor('brown');
$Plot[$i]->setTitle($activities[$i]); 
}
else if ($activities[$i] == "Pretest"){
$Plot[$i] =& $Plotarea->addNew('Image_Graph_Plot_Dot', array(&$Dataset[$i]));
$Marker2 =& Image_Graph::factory('Image_Graph_Marker_Asterisk');
$Marker2->setFillColor('cyan');
$Marker2->setSize(6);
$Marker2->setLineColor('cyan');
$Plot[$i]->setMarker($Marker2);
$Plot[$i]->setTitle($activities[$i]); 
}
else if ($activities[$i] == "Pretest|"){
$Plot[$i] =& $Plotarea->addNew('Plot_Impulse', array(&$Dataset[$i]));
$Plot[$i]->setLineColor('cyan');
$Plot[$i]->setTitle($activities[$i]); 
}
else
{
$linestyle =& Image_Graph::factory('Image_Graph_Line_Solid',array_shift($colors));
$linestyle->setThickness(3); 
$Plot[$i] =& $Plotarea->addNew('line', array(&$Dataset[$i]));
$Plot[$i]->setLineStyle($linestyle);
$Plot[$i]->setTitle($activities[$i]); 
}
       foreach ($datings[$i] as $daty){
		$Dataset[$i]->addPoint($daty->stamp, $daty->value);
}
}

$Font =& $Graph->addNew('ttf_font', '/usr/share/fonts/truetype/freefont/FreeMonoBold');
$Font->setSize(12);
$Graph->setFont($Font);

$Plotarea->Image_Graph_Plotarea('Image_Graph_Axis','Image_Graph_Axis');
$Plotarea->setAxisPadding(5, 'bottom');
$Plotarea->setAxisPadding(25, 'left');
$Plotarea->setAxisPadding(25, 'right');
$Plotarea->setAxisPadding(25, 'top');
$YAxis =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
$XAxis =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
$XAxis->setTitle('Date');
include_once 'Image/Graph/DataPreprocessor/Date.php';
$dateFormatter = new Image_Graph_DataPreprocessor_Date("m/d");
$XAxis->setDataPreProcessor(&$dateFormatter);
$Graph->done( 
array('filename' => '../wp-content/uploads/'.$current_user->user_login.''.$table_id.'_abagraph.png')
);
}

function get_table_name_abagraph($table_id) {
global $wpdb,$current_user;
$table_name = $wpdb->get_var("SELECT DISTINCT table_name FROM {$wpdb->prefix}abagraph WHERE user_id={$current_user->data->ID} and table_id=$table_id;");
return $table_name;
}

function abagraph_install() {
	

	global $wpdb;
	if (!current_user_can('activate_plugins')) return;
	$table_name1 = $wpdb->prefix . 'abagraph';
	$table_name2 = $wpdb->prefix . 'activities';

	// if activities table doesn't exist, create it 
	if ( $wpdb->get_var("show tables like '$table_name2'") != $table_name2) {
	$sql= "CREATE TABLE $table_name2 (
  	ID bigint(20) PRIMARY KEY AUTO_INCREMENT,
  	activity varchar(250) NOT NULL,
	UNIQUE KEY activity (activity))";
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);
	$insert_sql = "INSERT INTO $table_name2 (ID,activity) values (1,'Generalization'),(2,'Interobserver Agreement'),(3,'Pretest'),(4,'Baseline'),(5,'Pretest|'),(6,'Criteria Line'),(7,'Condition Line');";
	$wpdb->query($insert_sql);
	}

	// if abagraph table doesn't exist, create it 
	if ( $wpdb->get_var("show tables like '$table_name1'") != $table_name1 ) {
		$sql = "CREATE TABLE $table_name1 (
			id int PRIMARY KEY AUTO_INCREMENT,
			activity varchar(250) NOT NULL,
                        user_id bigint(20) NOT NULL,
                        table_id int NOT NULL,
                        table_name varchar(250) NOT NULL,
			stamp int NOT NULL,
			value double NOT NULL)";
		require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
		dbDelta($sql);
	}
}

register_activation_hook(__FILE__,'abagraph_install');

function aba_managePanel() {
	if (function_exists('add_management_page')) {
		add_management_page('ABAgraph','ABAgraph',7,basename(__FILE__),'aba_show_manage_panel');
	}
}

add_action('admin_menu','aba_managePanel');

function aba_show_manage_panel() {
global $wpdb, $current_user, $wp_version;
$table_prefix = $wpdb->prefix;
if (isset($_GET['abagraph_delete'])) { 
$item_id = $_GET['abagraph_delete'];
$table_id=$wpdb->get_var("SELECT table_id FROM ".$table_prefix."abagraph WHERE id=".$item_id." AND user_id={$current_user->data->ID}");
$sql = "DELETE FROM ".$table_prefix."abagraph WHERE id=".$item_id." AND user_id=".$current_user->data->ID;
$wpdb->query($sql);
}


if (isset($_POST['abagraph_value'])) {
// insert data here
$date = strtotime($_POST['abagraph_year']."-".$_POST['abagraph_month']."-".$_POST['abagraph_day']);
$value = $wpdb->escape($_POST['abagraph_value']);
$table_id = array_shift(split(':',$wpdb->escape($_POST['abagraph_table_id'])));
$table_name = trim(array_pop(split(':',$wpdb->escape($_POST['abagraph_table_id']))));
$activity = $wpdb->escape($_POST['abagraph_activity']);
$sql = "INSERT INTO ".$table_prefix."abagraph (activity, user_id, table_id, table_name, stamp, value) values ('$activity',{$current_user->data->ID},$table_id,'$table_name',$date,$value)";
if (!empty($value)){
$wpdb->query($sql);
}
$table_name=get_table_name_abagraph($table_id);
if (empty($table_name)) {
?>
<div class="error"><p><strong><?php _e('Create New? Please Enter a Value.');
?></strong></p></div><?php
} else {
draw_abagraph($table_prefix, $current_user,$table_id,$table_name);
} 
}
if (isset($_POST['graph_table_name'])) {
$graph_table_id = $wpdb->escape($_POST['graph_table_id']);
$graph_table_name = $wpdb->escape($_POST['graph_table_name']);
$sql = "UPDATE ".$table_prefix."abagraph set table_name='$graph_table_name' WHERE user_id={$current_user->data->ID} and table_id=$graph_table_id";
$wpdb->query($sql);
}
?>

<div class="wrap">
<h2>Add/Delete Activity</h2>
<form method="post">
<fieldset class="options">
<table class="editform optiontable">

        <tr>
                <th scope="row"><?php _e('Add') ?>:</th>
                <td><input name="activity" type="text" /></td>
                <?php $activity=trim($wpdb->escape($_POST['activity'])); 
if (!empty($activity)) {
$wpdb->query("INSERT INTO {$wpdb->prefix}activities (activity) VALUES ('$activity')"); 
}
?>
</tr>
<?php
$activity = $wpdb->escape($_POST['abagraph_activity_delete']);
if (!empty($_POST['abagraph_activity_delete'])) {
$wpdb->query("DELETE FROM {$wpdb->prefix}activities where activity=('$activity')"); 
}
?>
<tr>
<th scope="row"><?php _e('Delete'); ?>:</th>
<td><select name="abagraph_activity_delete">
<option value="" selected="selected">None</option> 
<?php
$activities= $wpdb->get_col("SELECT activity FROM {$wpdb->prefix}activities ORDER BY activity");
foreach ($activities as $activity) 
{
echo "<option value='$activity'>$activity</option>";
}

?>
</select>
</td>
</tr>
</table>
</fieldset>
<p align="right">
        <input name="addactivity" type="submit" value="<?php _e('Add/Delete Activity &raquo;') ?>" />
</p>
</form>
</div>

<div class="wrap">
<h2>Update/Display Graph</h2>
<form method="post">
<fieldset class="options">
<table class="editform optiontable">
<tr>
<th scope="row"><?php _e('Graph'); ?>:</th>
<td><select name="abagraph_table_id"><?php
$tables = $wpdb->get_results("SELECT table_id,table_name FROM {$wpdb->prefix}abagraph WHERE user_id={$current_user->data->ID} group BY table_name ASC;");
$high_table = 0;
foreach ($tables as $table) {
	$sel = '';
	if (isset($_POST['abagraph_table_id']))
		if ($table->table_id == $_POST['abagraph_table_id'])
			$sel = ' selected="selected"';
	echo '<option value="'.$table->table_id.': '.$table->table_name.'"'.$sel.'>'.$table->table_id.': '.$table->table_name.'</option>';
	if ($table->table_id>$high_table)
		$high_table = $table->table_id;
}
$high_table++;
echo '<option value="'.$high_table.'">'.$high_table.': (Create new)</option>';
?></select>
</td>
</tr>

<tr>
<th scope="row"><?php _e('Variable'); ?>:</th>
<td><select name="abagraph_activity">
<?php
$activities= $wpdb->get_col("SELECT activity FROM {$wpdb->prefix}activities ORDER BY activity");
foreach ($activities as $activity) 
{
echo "<option value='$activity'>$activity</option>";
}
?>
</select>
</td>
</tr>

<tr>
<th scope="row"><?php _e('Date'); ?>:</th>
<td>
<select name="abagraph_month"><?php
for ($m = 1; $m<=12; $m++) { ?>
<option value="<?php printf("%02d",$m); ?>"<?php if ($m==date("m")) echo 
" selected=\"selected\""; ?>><?php printf("%02d",$m); ?></option>
<?php } ?></select>
<select name="abagraph_day"><?php
for ($m = 1; $m<=31; $m++) { ?>
<option value="<?php printf("%02d",$m); ?>"<?php if ($m==date("d")) echo 
" selected=\"selected\""; ?>><?php printf("%02d",$m); ?> &nbsp; </option>
<?php } ?></select>
<select name="abagraph_year"><?php
$year = date("Y")-2;
for ($y = $year; $y<$year+5; $y++) { ?>
<option value="<?php echo $y; ?>"<?php if ($y==($year+2)) echo " selected=\"selected\"";?>><?php echo $y; ?></option>
<?php } ?></select>
</td></tr>
<tr>
<th scope="row"><?php _e('Value'); ?>:</th>
<td><input type="text" name="abagraph_value" /></td>
</tr>
</table>
</fieldset>
<p align="right">
<input type="submit" name="graph_insert" value="<?php _e('Update/Display Graph'); ?> &raquo;" />
</p>
</form>
</div>

<?php
if (!empty($tables)) {
?>

<div class="wrap">
<h2>Set Title</h2>
<form method="post">
<fieldset class="options">
<table class="editform optiontable">
<tr>
<th scope="row"><?php _e('Graph#'); ?>:</th>
<td><select name="graph_table_id"><?php
$tables = $wpdb->get_results("SELECT DISTINCT(table_id) FROM {$wpdb->prefix}abagraph WHERE user_id={$current_user->data->ID} ORDER BY table_id ASC;");
$high_table = 0;
foreach ($tables as $table) {
        $sel = '';
        if (isset($_POST['graph_table_id']))
                if ($table->table_id == $_POST['graph_table_id'])
                        $sel = ' selected="selected"';
        echo '<option value="'.$table->table_id.'"'.$sel.'>'.$table->table_id.'</option>';
        if ($table->table_id>$high_table)
                $high_table = $table->table_id;
}
?></select>
</td>
</tr>

<tr>
<th scope="row"><?php _e(' Title'); ?>:</th>
<td><select name="graph_table_name">
<?php
$activities= $wpdb->get_col("SELECT activity FROM {$wpdb->prefix}activities ORDER BY activity");
foreach ($activities as $activity)
{
echo "<option value='$activity'>$activity</option>";
}
?>
</select>
</td>
</tr>
</table>
<p align="right">
<input type="submit" name="addtablename" value="<?php _e('Set Title'); ?> &raquo;" />
</p>
</form>
</div>

<?php
}

if (!empty($table_id)) {
$table_name=get_table_name_abagraph($table_id);
}
if (!empty($table_name)) { ?>
<div class="wrap">
<table id="the-list-x" width="100%" cellpadding="3" cellspacing="3">
<tr><th align="left">Graph#</th><th align="left">Variable</th><th align="left">Date</th><th align="left">Value</th></tr>
<?php
$sql = "SELECT * FROM ".$table_prefix."abagraph WHERE user_id={$current_user->data->ID} and table_id=$table_id ORDER BY activity DESC, stamp DESC";
$valueset = $wpdb->get_results($sql); 
	foreach ($valueset as $values) { 
		$class = ('alternate' == $class) ? '' : 'alternate'; ?>
<tr id="post-"<?php echo $values->id; ?>" class="<?php echo $class; ?>">
<td><?php echo $values->table_id; ?></td>
<td><?php echo $values->activity; ?></td>
<td><?php echo date("m.d.Y",$values->stamp); ?></td>
<td><?php echo $values->value; ?></td>
<?php
if ($wp_version == "2.7") {
?>
<td><a href="tools.php?page=abagraph.php&amp;abagraph_delete=<?php echo $values->id; ?>"" onClick="return confirm('Delete Value?')""><?php _e(' Delete'); ?></a></td>
<?php
}
else {
?>
<td><a href="edit.php?page=abagraph.php&amp;abagraph_delete=<?php echo $values->id; ?>"" onClick="return confirm('Delete Value?')""><?php _e(' Delete'); ?></a></td>
<?php
}
?>
</tr>	
<?php 
}
draw_abagraph($table_prefix, $current_user,$table_id,$table_name);
?>
</table>
<?php $browser = $_SERVER['HTTP_USER_AGENT'];
$rowcount = count($valueset);
if ($rowcount < 2) {
?>
<div class="wrap"><p><?php _e('Graph will appear after another value is entered');
?></p></div><?php
}
if ($rowcount > 1) {
if (empty($current_user->first_name) & empty($current_user->last_name)){
?>
<div class="error"><p><strong><?php echo "No First and Last Name Showing in Graph Title. Please go to My Profile to enter First and Last Name for $current_user->user_login.";
?></strong></p></div><?php
}
$table_name=get_table_name_abagraph($table_id);
if (is_numeric($table_name)) {
?>
<div class="error"><p><strong><?php echo "Graph Title is '$table_name'. Please Use Set Title Above";
?></strong></p></div><?php
}
if (eregi('iPhone', $browser)) { ?>
<center><img width="100%" src="<?php echo '../wp-content/uploads/'.$current_user->user_login.''.$table_id.'_abagraph.png';?>"></center>
<?php
}
else {
?>
<center><img src="<?php echo '../wp-content/uploads/'.$current_user->user_login.''.$table_id.'_abagraph.png';?>"></center>
<?php }
}
}
?>
</div>
<?php
}
?>
