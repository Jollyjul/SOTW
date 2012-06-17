<?php
require '../Slim/Slim.php';

$app = new Slim();
$app->config('templates.path','../views/'); 

$app->get('/text/:file.txt', function($file) use ($app){
    echo file_get_contents('./text/'.$file.'.txt');
});

$app->get('/search.json/:volume/:query', function($volume,$query) use ($app) {
    $response = $app->response();
    $response['Content-Type'] = 'application/json';

    $results = array();
    $dir = "./$volume/text";
    if ($handle = opendir($dir)) {
        while ($f = readdir($handle))
        {    
            if (is_file("$dir/$f"))
            {
                $str = file_get_contents("$dir/$f");
                preg_match("/([0-9].+)/",$f,$matches,PREG_OFFSET_CAPTURE);
                $parts = explode('_',$matches[0][0]);
                $page_parts = explode(".",$parts[1]);
                $page = $page_parts[0];
                
                $words = str_replace('.','',str_replace('!','',str_replace('?','',str_replace(',','',strtolower($str)))));
                if(in_array(strtolower($query),explode(' ',$words)))
                {
                    $results[] = intval($page);
                }            
            }
        }
    closedir($handle);
    }
    $o = new stdclass();
    $o->results = $results;
    $o->query = $query;
    echo json_encode($o);
});

$app->get('/', function () {
    $app = Slim::getInstance();
    $app->render('../views/index.phtml');
});
$app->get('/viewer.erb', function () {
    $app = Slim::getInstance();
    $app->render('../views/viewer.phtml');
});
$app->run();
