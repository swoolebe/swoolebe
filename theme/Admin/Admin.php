<?php
namespace Theme\Admin;


use System\Be;
use System\Template;

/**
 * 模板基类
 */
class Admin extends Template
{

    public function display()
    {
        $config = Be::getConfig( 'System');
        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
            <meta name="description" content="<?php echo $this->metaKeywords;?>" />
            <meta name="keywords" content="<?php echo $this->metaDescription;?>" />
            <title><?php echo $this->title.' - '.$config->siteName; ?></title>

            <script type="text/javascript" language="javascript" src="/theme/Admin/js/jquery-1.11.0.min.js"></script>
            <script type="text/javascript" language="javascript" src="/theme/Admin/js/jquery.validate.min.js"></script>

            <?php $this->head(); ?>

        </head>
        <body>
        <div class="theme-body-container">
            <div class="theme-body">
                <?php
                $this->body();
                ?>
            </div>
        </div>
        </body>
        </html>
        <?php
    }



    /**
     *
     * 主体
     */
    protected function body()
    {
        ?>
        <div class="theme-north-container">
            <div class="theme-north">
                <?php
                $this->north();
                ?>
            </div>
        </div>

        <div class="theme-middle-container">
            <div class="theme-middle">
                <?php
                $this->middle();
                ?>
            </div>
        </div>

        <div class="theme-south-container">
            <div class="theme-south">
                <?php
                $this->south();
                ?>
            </div>
        </div>
        <?php
    }

    /**
     *
     * 项部
     */
    protected function north()
    {
    }


    protected function middle()
    {
        ?>
        <div class="row">

            <div class="col" style="width:20%;">
                <div class="theme-west-container">
                    <div class="theme-west">
                        <?php $this->west(); ?>
                    </div>
                </div>
            </div>


            <div class="col" style="width:70%;">

                <div class="theme-center-container">
                    <div class="theme-center">
                        <?php
                        $this->center();
                        ?>
                    </div>
                </div>

            </div>

            <div class="clear-left"></div>
        </div>
        <?php
    }

    /**
     *
     * 底部
     */
    protected function south()
    {
    }





}
