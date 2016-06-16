<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Example</title>
</head>

<body>

<style type="text/css">
    body {
        padding:0;
        margin: 0;
        font-family: "Lucida Sans Unicode", Arial;
        font-size: 14px;
    }

    #site {
        margin: 0 auto;
        width: 600px;
        padding: 30px 0 0 0;
        color:#65615E;
    }

    h1, h2, h3 {
        font-size: 18px;
        padding: 0 0 5px 0;
        border-bottom: 1px solid #001428;
        margin-bottom: 5px;
    }

    h3 {
        font-size: 14px;
        padding: 15px 0 5px 0;
        margin-bottom: 5px;
        border-color: #cccccc;
    }

    img {
        border: 0;
    }

    p {
        padding: 0 0 5px 0;
    }

    a {
        color: #000;
    }

    #logo {
        text-align: center;
        padding: 50px 0;
    }

    #logo hr {
        display: block;
        height: 1px;
        overflow: hidden;
        background: #BBB;
        border: 0;
        padding:0;
        margin:30px 0 20px 0;
    }

    .claim {
        text-transform: uppercase;
        color:#BBB;
    }

    #site ul {
        padding: 10px 0 10px 20px;
        list-style: circle;
    }

    .buttons {
        margin-bottom: 100px;
        text-align: center;
    }

    .buttons a {
        display: inline-block;
        background: #0078be;
        color:#fff;
        padding: 5px 10px;
        margin-right: 10px;
        width:40%;
        border-radius: 2px;
        text-decoration: none;
    }

    .buttons a:hover {
        background: #1C8BC1;
    }

    .buttons a:last-child {
        margin: 0;
    }

</style>


<div id="site">
    <div id="logo">
        <a href="http://www.pimcore.org/"><img src="/pimcore/static6/img/logo-gray.svg" style="width: 200px;" /></a>
        <hr />
        <div class="claim">
            THE OPEN-SOURCE ENTERPRISE PLATFORM FOR PIM, CMS, DAM & COMMERCE
        </div>
    </div>

    <?php if($this->editmode) { ?>
        <div class="buttons">
            <a target="_blank" href="https://www.pimcore.org/wiki/pages/viewpage.action?pageId=16854186">Install Sample Data / Boilerplate</a>
            <a target="_blank" href="http://www.pimcore.org/wiki/display/PIMCORE4/Develop+with+pimcore">Getting Started</a>
        </div>

        <div class="info">
            <h2>Where can I edit some pages?</h2>
            <p>
                Well, it seems that you're using the professional distribution of pimcore which doesn't include any
                templates / themes or contents, it's designed to start a project from scratch. If you need a jump start
                please consider using our sample data / boilerplate package which includes everything you need to get started.
            </p>
        </div>
    <?php } ?>
</div>

</body>
</html>
