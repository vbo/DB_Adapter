<?php
/**
 * DB_Adapter documentation browser
 *
 * @package DB_Adapter
 * @subpackage DocBrowser
 *
 * DB_Adapter PHP library provides elegant interface for some SQL databases.
 * It supports several types of handy and secure placeholders
 * and provide comfortable debugging.
 *
 * (c) DB_Adapter community
 * @see http://db-adapter.in-source.ru
 *
 * Original idea by Dmitry Koterov and Konstantin Zhinko
 * @see http://dklab.ru/lib/DbSimple/
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * @see http://www.gnu.org/copyleft/lesser.html
 *
 * @author  Borodin Vadim <vb@in-source.ru>
 * @version 0.1 beta
 */

error_reporting(E_ALL);
ini_set('display_errors','On');
require_once 'lib/config.php';

(include_once 'markdown/markdown.php') or die('
    <br /><b>Check your Markdown installation</b> <br />
    <em>PHP Markdown Extra</em> must be installed and findable in the PATH.
    We recommend just install it in the "lib" directory.<br />
    <a href="http://michelf.com/projects/php-markdown/">See also &rarr;</a>'
);

$request_uri = array_shift(
    explode('?', $_SERVER['REQUEST_URI'])
);

if ($request_uri == '/') $request_uri = '/frontpage/';

// URI must be trailed by slash
if ($request_uri[strlen($request_uri)-1] != '/') {
    header("Location: {$request_uri}/");
    die();
}

$request_uri  = substr($request_uri, 1, -1);
$request_file = determine_request_file($request_uri);
if (!file_exists($request_file)) {
    header('HTTP/1.1 404 Not Found');
    include('404.php');
    die();
}

$fcontents   = file($request_file);
$page_header = trim(@$fcontents[0]);
$fcontents   = join('', $fcontents);

if (@$_GET['view'] == 'text') {
    header('Content-type: text/plain');
    echo $fcontents;
} else {
    $breadcrumbs = create_breadcrumbs($request_uri, $page_header);
    $title       = create_title($breadcrumbs, $page_header);

    render(array(
        'title'       => $title,
        'content'     => Markdown($fcontents),
        'breadcrumbs' => $breadcrumbs,
        'links'       => array(
            'view_source' => "{$_SERVER['REQUEST_URI']}?view=text",
        ),
    ));
}


######################################################

function determine_request_file ($uri)
{
    return "doc/{$uri}.text";
}

function render ($__vars)
{
    extract($__vars);
    require 'inc/template.php';
}

function create_breadcrumbs ($request_uri, $title)
{
    $breadcrumbs     = array();
    $arr_request_uri = explode('/', $request_uri);
    array_pop($arr_request_uri);

    while(!empty($arr_request_uri)) {
        $bc = array();
        $ru = '/'.join('/', $arr_request_uri);
        $f  = determine_request_file($ru);
        $fi = fopen($f, "r");

        if (!$fi) break;
        $stitle = trim(@fgets($fi));

        $bc['uri']   = $ru . '/';
        $bc['title'] = $stitle;

        array_pop($arr_request_uri);
        array_unshift($breadcrumbs, $bc);
    }

    if ($request_uri != 'frontpage') {
        $breadcrumbs[] = array(
            'uri'   => "/{$request_uri}/",
            'title' => $title,
        );
    }

    return $breadcrumbs;
}

function create_title ($breadcrumbs, $page_header)
{
    $t = empty($breadcrumbs) ? $page_header : 'DB_Adapter';
    foreach ($breadcrumbs as $bc) $t .= " :: {$bc['title']}";
    return $t;
}