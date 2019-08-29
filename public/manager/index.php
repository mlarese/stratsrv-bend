<?php
/**
 * Created by PhpStorm.
 * User: mauro.larese
 * Date: 29/08/2019
 * Time: 18:44
 */
echo "index";
?>
$db = new \MicroDB\Database('data/posts'); // data directory

// create an item
// id is an auto incrementing integer
$id = $db->create(array(
'title' => 'Lorem ipsum',
'body' => 'At vero eos et accusam et justo duo dolores et ea rebum.'
));

// load an item
$post = $db->load($id);

// save an item
$post['tags'] = array('lorem', 'ipsum');
$db->save($id, $post);

// find items
$posts = $db->find(function($post) {
return is_array(@$post['tags']) && in_array('ipsum', @$post['tags']);
});

foreach($posts as $id => $post) {
print_r($post);
}

// delete an item
$db->delete($id);
