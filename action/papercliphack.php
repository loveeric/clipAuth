<?php
/**
 * DokuWiki Plugin papercliphack (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Tongyu Nie <marktnie@gmail.com>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_clipauth_papercliphack extends DokuWiki_Action_Plugin
{
    var $pdo;
    var $settings;
    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     *
     * @return void
     */
    public function register(Doku_Event_Handler $controller)
    {
        require  dirname(__FILE__).'/../settings.php';
        $dsn = "mysql:host=".$this->settings['host'].
            ";dbname=".$this->settings['dbname'].
            ";port=".$this->settings['port'].
            ";charset=".$this->settings['charset'];

        try {
            $this->pdo = new PDO($dsn, $this->settings['username'], $this->settings['password']);
        } catch ( PDOException $e) {
            echo "Datebase connection error";
            exit;
        }

        $controller->register_hook('COMMON_WIKIPAGE_SAVE', 'AFTER', $this, 'handle_common_wikipage_save');
        $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'handle_tpl_content_display');
//        $controller->register_hook('HTML_REGISTERFORM_OUTPUT', 'AFTER', $this, 'handle_html_registerform_output');
//        $controller->register_hook('HTML_LOGINFORM_OUTPUT', 'AFTER', $this, 'handle_html_loginform_output');
//        $controller->register_hook('HTML_UPDATEPROFILEFORM_OUTPUT', 'AFTER', $this, 'handle_html_updateprofileform_output');
   
    }

    /**
     * [Custom event handler which performs action]
     *
     * Called for event:
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     *
     * @return void
     */
    public function handle_common_wikipage_save(Doku_Event $event, $param)
    {
        global $INFO;
        $pageid = $event->data['id'];
        $summary = $event->data['summary'];
        $editor = $INFO['userinfo']['name'];
        $sql = 'insert into '.$this->settings['editlog'].' (id, pageid, time, summary, editor)
            values
                (null, :pageid, null, :summary, :editor)';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':pageid', $pageid);
        $statement->bindValue(':summary', $summary);
        $statement->bindValue(':editor', $editor);
        $result = $statement->execute();
        if ($result === false) {
            echo 'log error';
            exit;
        }


    }

    /**
     * Print a row of edit log unit
     * Author: Tongyu Nie marktnie@gmail.com
     * @param $editData
     *
     */
    private function editUnit($editData) {
        $pageid = $editData['pageid']; // Fix me. Need fix to provide right info, so do other variables
        $time   = $editData['time'];
        $summary= $editData['summary'];
        $editor = $editData['editor'];

        print "
<div class='paperclip__editlog__unit'>
    <hr class='paperclip__editlog__split'>
    <div class='paperclip__editlog__header'>
        <div class='paperclip__editlog__pageid'>
           $pageid 
        </div>
        <div class='paperclip__editlog__time'>
            $time
        </div>
    </div> 
    <p class='paperclip__editlog__sum'>
        $summary
    </p>
    <div class='paperclip__editlog__footer'>
        <a class='paperclip__editlog__link' href='/doku.php?id=$pageid&show=edit'>继续编辑</a>
        <a class='paperclip__editlog__link' href='/doku.php?id=$pageid'>查看当前条目</a>
        <div class='paperclip__editlog__index'>
            索引
            <ul></ul>
        </div>
    </div>
</div> 
        ";
    }
    /*
     * number
     */
    private function countEditForName($username) {
        $sql = 'select count(*) from '.$this->settings['editlog']. ' where editor = '.$username;
        $result = $this->pdo->query($sql);

        if ($result === false) return 1;
        $num = $result->fetchColumn();
        return $num;
    }
    /**
     * Return legal pagenum
     */
    private function checkPagenum($pagenum, $count, $username) {
        global $conf;
        $num = $count;
        $maxnum = ceil($num / $this->getConf('editperpage'));
        if ($pagenum > $maxnum) {
            $pagenum = $maxnum;
        } elseif ($pagenum < 1) {
            $pagenum = 1;
        }

        return $pagenum;
    }

    private function editlog($pagenum) {
        // Fix me. Here we out put the content of edit history
        global $USERINFO, $conf;
        $username = $USERINFO['name'];
        $count = $this->countEditForName($username);
        $pagenum = $this->checkPagenum($pagenum, $count, $username);
        $offset = ($pagenum - 1) * $this->getConf('editperpage');
        $count = $this->getConf('editperpage');

        $sql = 'select * from '.$this->settings['editlog'].' where editor=:editor order by id DESC limit :offset ,:count';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':editor', $username);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->bindValue(':count', $count, PDO::PARAM_INT);
        $statement->execute();

        while (($result = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            // Processing the result of editlog, generating a row of log
            $this->editUnit($result);
        }


    }

    private function comment($pagenum) {
        // Fix me. Here we out put the content of comment page
    }

    private function setting() {
        // Fix me. Here we out put the content of user setting

    }
    public function handle_tpl_content_display(Doku_Event $event, $param)
    {
        global $_GET;
        $show = $_GET['show'];
        if ($show === 'editlog') {
            $pagenum = $_GET['page'];
            $this->editlog($pagenum);
        } else if ($show === 'comment') {
            echo 'comment';
        } else if ($show === 'setting') {
            echo 'setting';
        }
        exit;
    }
    public function handle_html_registerform_output(Doku_Event $event, $param)
    {
    }
    public function handle_html_loginform_output(Doku_Event $event, $param)
    {
    }
    public function handle_html_updateprofileform_output(Doku_Event $event, $param)
    {
    }

}

