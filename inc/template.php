<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?php echo $title?></title>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />

        <link rel="stylesheet" href="/public/defaults.css" media="all" />
        <link rel="stylesheet" href="/public/styles.css"   media="all" />

        <link rel="icon"          href="/public/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="/public/favicon.ico" type="image/x-icon" />
        
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
        
        <?php $thisPage = array_pop($breadcrumbs); $rootPage = @$breadcrumbs[0]; ?>
        <?php if (!is_null($thisPage)):?>
            <script type="text/javascript">
                $(document).ready(
                    function () {
                        $('a[href=<?php echo $thisPage['uri']?>]').addClass('this');
                        <?php if ($rootPage):?>
                            $('a[href=<?php echo $rootPage['uri']?>]').addClass('root');
                        <?php endif;?>
                    }
                );
            </script>
        <?php else:?>
        <?php endif;?>
    </head>
    <body>
        <div id="wrap">
            <div class="holder">
                <h1 class="top_header">
                    <a class="m" href="http://db-adapter.vbo.name/"
                       title="DB_Adapter &mdash; единый элегантный PHP-интерфейс для различных СУБД">
                        <img alt="DB_Adapter PHP Library" src="/public/images/db_adapter_logo.jpg" />
                    </a>
                    <span id="breadcrumbs">
                        <?php if (!is_null($thisPage)):?>
                            <?php foreach($breadcrumbs as $bc):?>
                                <a href="<?php echo $bc['uri']?>"><?php echo $bc['title']?></a>
                                &rarr;
                            <?php endforeach;?>

                            <a class="this" href="<?php echo $thisPage['uri']?>"><?php echo $thisPage['title']?></a>
                        <?php endif;?>
                    </span>
                </h1>

                <div id="hat_ad">
                    
                </div>

                <div class="menu">
                    <div class="yandexform" onclick="return {type: 3, logo: 'rb', arrow: false, webopt: false, websearch: false, bg: '#FFA000', fg: '#000000', fontsize: 14, suggest: true, site_suggest: true, encoding: 'utf-8'}"><form action="http://yandex.ru/sitesearch" method="get"><input type="hidden" name="searchid" value="158888"/><input name="text"/><input type="submit" value="Найти"/></form></div><script type="text/javascript" src="http://site.yandex.net/load/form/1/form.js" charset="utf-8"></script>

                    <a href="http://github.com/vbo/DB_Adapter">Проект на github</a>
                    <!--a href="/tests/">Тесты</a-->
                    <a href="/dev/">Разработчикам</a>
                    <a href="/docs/">Документация</a>
                    <a href="/download/">Скачать</a>
                </div>

                <div id="body">
                    <div id="sbody">
                        <div id="ssbody">
                            <div id="docs_layout">

                                <div class="toc">
                                    <?php include 'toc.php';?>

                                    <!-- Orphus -->
                                    <script type="text/javascript" src="/public/orphus.js"></script>
                                    <a href="http://orphus.ru" id="orphus" target="_blank">
                                        <img alt="Система Orphus" src="/public/orphus.gif" />
                                    </a>
                                    <!-- /Orphus -->

                                    <!-- W3C "valid" icons -->
                                    <p id="its_valid">
                                        <a href="http://jigsaw.w3.org/css-validator/check/referer">
                                        <img src="http://jigsaw.w3.org/css-validator/images/vcss-blue"
                                        alt="Правильный CSS!" /></a>

                                        <a href="http://validator.w3.org/check?uri=referer"><img
                                        src="http://www.w3.org/Icons/valid-xhtml10-blue"
                                        alt="Valid XHTML 1.0 Transitional" /></a>
                                    </p>
                                    <!-- W3C "valid" icons -->
                                </div>

                                <div class="content">
                                    <div id="coholder">
                                        <?php echo $content?>
                                        <div class="meta_ui">
                                            <a class="view_source" title="<?php echo $title?>" href="<?php echo $links['view_source']?>">Исходник &rarr;</a>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>

                <div id="footer">
                    <div id="partners">
                        <a href="http://github.com/vbo/DB_Adapter"><img style="width: 120px;" src="/public/images/github_logo.png" alt="github" /></a>
                    </div>
                    <ul>
                        <li><a href="http://db-adapter.vbo.name/">Официальный сайт DB_Adapter</a></li>
                        <li><a href="http://github.com/vbo/DB_Adapter">Проект на github</a></li>
                        <li><a href="http://www.gnu.org/licenses/lgpl-3.0.txt">Лицензия GNU LGPL</a></li>
                        <li>(c) 2010</li>
                    </ul>

                    <a href="http://www.opensource.org/" class="opensource">
                        <img alt="DB_Adapter является Открытым и Свободным Программным Обеспечением"
                             src="/public/images/opensource_logo.png" />
                    </a>
                    
                    <!-- Yandex.Metrika -->
                    <!-- /Yandex.Metrika -->
                </div>
            </div>
        </div>
    </body>
</html>