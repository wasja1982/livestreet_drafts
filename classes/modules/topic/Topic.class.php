<?php
/**
 * Drafts - доступ к черновикам пользователей
 *
 * Версия:	1.0.1
 * Автор:	Александр Вереник
 * Профиль:	http://livestreet.ru/profile/Wasja/
 * GitHub:	https://github.com/wasja1982/livestreet_drafts
 *
 **/

class PluginDrafts_ModuleTopic extends PluginDrafts_Inherit_ModuleTopic {
    /**
     * список топиков из персональных блогов
     *
     * @param int $iPage	Номер страницы
     * @param int $iPerPage	Количество элементов на страницу
     * @param string $sShowType	Тип выборки топиков
     * @param string|int $sPeriod	Период в виде секунд или конкретной даты
     * @return array
     */
    public function GetTopicsPersonal($iPage,$iPerPage,$sShowType='good',$sPeriod=null) {
        $access = ($this->User_IsAuthorization() and $this->oUserCurrent->isAdministrator());
        if (!$access || $sShowType != 'draft') {
            return parent::GetTopicsPersonal($iPage,$iPerPage,$sShowType,$sPeriod);
        } else {
            $aFilter=array(
                'blog_type' => array('personal'),
                'topic_publish' => 0,
            );
            return $this->GetTopicsByFilter($aFilter,$iPage,$iPerPage);
        }
    }
    /**
     * Список топиков из коллективных блогов
     *
     * @param int $iPage	Номер страницы
     * @param int $iPerPage	Количество элементов на страницу
     * @param string $sShowType	Тип выборки топиков
     * @param string $sPeriod	Период в виде секунд или конкретной даты
     * @return array
     */
    public function GetTopicsCollective($iPage,$iPerPage,$sShowType='good',$sPeriod=null) {
        $access = ($this->User_IsAuthorization() and $this->oUserCurrent->isAdministrator());
        if (!$access || $sShowType != 'draft') {
            return parent::GetTopicsCollective($iPage,$iPerPage,$sShowType,$sPeriod);
        } else {
            $aFilter=array(
                'blog_type' => array('open'),
                'topic_publish' => 0,
            );
            /**
             * Если пользователь авторизирован, то добавляем в выдачу
             * закрытые блоги в которых он состоит
             */
            if($this->oUserCurrent) {
                $aOpenBlogs = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
                if(count($aOpenBlogs)) $aFilter['blog_type']['close'] = $aOpenBlogs;
            }
            return $this->GetTopicsByFilter($aFilter,$iPage,$iPerPage);
        }
    }
    /**
     * Список топиков из блога
     *
     * @param ModuleBlog_EntityBlog $oBlog	Объект блога
     * @param int $iPage	Номер страницы
     * @param int $iPerPage	Количество элементов на страницу
     * @param string $sShowType	Тип выборки топиков
     * @param string $sPeriod	Период в виде секунд или конкретной даты
     * @return array
     */
    public function GetTopicsByBlog($oBlog,$iPage,$iPerPage,$sShowType='good',$sPeriod=null) {
        $access = ($this->User_IsAuthorization() and $this->oUserCurrent->isAdministrator());
        if (!$access || $sShowType != 'draft') {
            return parent::GetTopicsByBlog($oBlog,$iPage,$iPerPage,$sShowType,$sPeriod);
        } else {
            $aFilter=array(
                'topic_publish' => 0,
                'blog_id' => $oBlog->getId(),
            );
            return $this->GetTopicsByFilter($aFilter,$iPage,$iPerPage);
        }
    }
    /**
     * Список черновиков из всех блогов
     *
     * @param int $iPage	Номер страницы
     * @param int $iPerPage	Количество элементов на страницу
     * @return array
     */
    public function GetTopicsDraftAll($iPage,$iPerPage) {
        $access = ($this->User_IsAuthorization() and $this->oUserCurrent->isAdministrator());
        if ($access) {
            $aFilter=array(
                'blog_type' => array(),
                'topic_publish' => 0,
            );
            if (Config::Get('plugin.drafts.show_personal')) {
                $aFilter['blog_type'][] = 'personal';
            }
            if (Config::Get('plugin.drafts.show_blog')) {
                $aFilter['blog_type'][] = 'open';
            }
            /**
             * Если пользователь авторизирован, то добавляем в выдачу
             * закрытые блоги в которых он состоит
             */
            if($this->oUserCurrent) {
                $aOpenBlogs = $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent);
                if(count($aOpenBlogs)) $aFilter['blog_type']['close'] = $aOpenBlogs;
            }
            return $this->GetTopicsByFilter($aFilter,$iPage,$iPerPage);
        }
    }
	/**
	 * Получает список черновиков по юзеру
	 *
	 * @param int $sUserId	ID пользователя
	 * @param int $iPage	Номер страницы
	 * @param int $iPerPage	Количество элементов на страницу
	 * @return array
	 */
	public function GetDraftsPersonalByUser($sUserId,$iPage,$iPerPage) {
		$aFilter=array(
			'topic_publish' => 0,
			'user_id' => $sUserId,
			'blog_type' => array('open','personal','close'),
		);
		return $this->GetTopicsByFilter($aFilter,$iPage,$iPerPage);
	}
	/**
	 * Возвращает количество черновиков которые создал юзер
	 *
	 * @param int $sUserId	ID пользователя
	 * @return array
	 */
	public function GetCountDraftsPersonalByUser($sUserId) {
		$aFilter=array(
			'topic_publish' => 0,
			'user_id' => $sUserId,
			'blog_type' => array('open','personal','close'),
		);
		$s=serialize($aFilter);
		if (false === ($data = $this->Cache_Get("topic_draft_count_user_{$s}"))) {
			$data = $this->oMapperTopic->GetCountTopics($aFilter);
			$this->Cache_Set($data, "topic_draft_count_user_{$s}", array("topic_draft_update_user_{$sUserId}"), 60*60*24);
		}
		return 	$data;
	}
}
?>