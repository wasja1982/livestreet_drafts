<?php
/**
 * Drafts - доступ к черновикам пользователей
 *
 * Версия:	1.0.2
 * Автор:	Александр Вереник
 * Профиль:	http://livestreet.ru/profile/Wasja/
 * GitHub:	https://github.com/wasja1982/livestreet_drafts
 *
 **/

class PluginDrafts_ActionBlog extends PluginDrafts_Inherit_ActionBlog
{
    /**
     * Инизиализация экшена
     *
     */
    public function Init() {
        parent::Init();
        $this->aBadBlogUrl[] = 'draft';
    }

    /**
     * Регистрируем евенты, по сути определяем УРЛы вида /blog/.../
     *
     */
    protected function RegisterEvent() {
        if (Config::Get('plugin.drafts.show_blog')) {
            $this->AddEventPreg('/^draft$/i','/^(page([1-9]\d{0,5}))?$/i',array('EventTopics','topics'));
            $this->AddEventPreg('/^[\w\-\_]+$/i','/^draft$/i','/^(page([1-9]\d{0,5}))?$/i',array('EventShowBlog','blog'));
        }
        parent::RegisterEvent();
    }

    /**
     * Показ всех топиков
     *
     */
    protected function EventTopics() {
        $sShowType = $this->sCurrentEvent;
        if ($sShowType == 'draft') {
            if (!$this->User_GetUserCurrent() || !$this->User_GetUserCurrent()->isAdministrator()) {
                return parent::EventNotFound();
            }
        }
        return parent::EventTopics();
    }

    /**
     * Вывод топиков из определенного блога
     *
     */
    protected function EventShowBlog() {
        $sShowType=$this->GetParamEventMatch(0,0);
        if ($sShowType != 'draft') {
            return parent::EventShowBlog();
        }
        if (!$this->User_GetUserCurrent() || !$this->User_GetUserCurrent()->isAdministrator()) {
            return parent::EventNotFound();
        }
        $sBlogUrl=$this->sCurrentEvent;
        /**
         * Проверяем есть ли блог с таким УРЛ
         */
        if (!($oBlog=$this->Blog_GetBlogByUrl($sBlogUrl))) {
            return parent::EventNotFound();
        }
        /**
         * Определяем права на отображение закрытого блога
         */
        if($oBlog->getType()=='close'
            and (!$this->oUserCurrent
                or !in_array(
                    $oBlog->getId(),
                    $this->Blog_GetAccessibleBlogsByUser($this->oUserCurrent)
                )
            )
        ) {
            $bCloseBlog=true;
        } else {
            $bCloseBlog=false;
        }
        /**
         * Меню
         */
        $this->sMenuSubItemSelect=$sShowType;
        $this->sMenuSubBlogUrl=$oBlog->getUrlFull();
        /**
         * Передан ли номер страницы
         */
        $iPage= $this->GetParamEventMatch(1,2) ? $this->GetParamEventMatch(1,2) : 1;

        if (!$bCloseBlog) {
            /**
             * Получаем список топиков
             */
            $aResult=$this->Topic_GetTopicsByBlog($oBlog,$iPage,Config::Get('module.topic.per_page'),$sShowType,null);
            $aTopics=$aResult['collection'];
            /**
             * Формируем постраничность
             */
            $aPaging=$this->Viewer_MakePaging($aResult['count'],$iPage,Config::Get('module.topic.per_page'),Config::Get('pagination.pages.count'),$oBlog->getUrlFull().$sShowType,array());
            /**
             * Получаем число новых топиков в текущем блоге
             */
            $this->iCountTopicsBlogNew=$this->Topic_GetCountTopicsByBlogNew($oBlog);

            $this->Viewer_Assign('aPaging',$aPaging);
            $this->Viewer_Assign('aTopics',$aTopics);
        }
        /**
         * Выставляем SEO данные
         */
        $sTextSeo=strip_tags($oBlog->getDescription());
        $this->Viewer_SetHtmlDescription(func_text_words($sTextSeo, Config::Get('seo.description_words_count')));
        /**
         * Получаем список юзеров блога
         */
        $aBlogUsersResult=$this->Blog_GetBlogUsersByBlogId($oBlog->getId(),ModuleBlog::BLOG_USER_ROLE_USER,1,Config::Get('module.blog.users_per_page'));
        $aBlogUsers=$aBlogUsersResult['collection'];
        $aBlogModeratorsResult=$this->Blog_GetBlogUsersByBlogId($oBlog->getId(),ModuleBlog::BLOG_USER_ROLE_MODERATOR);
        $aBlogModerators=$aBlogModeratorsResult['collection'];
        $aBlogAdministratorsResult=$this->Blog_GetBlogUsersByBlogId($oBlog->getId(),ModuleBlog::BLOG_USER_ROLE_ADMINISTRATOR);
        $aBlogAdministrators=$aBlogAdministratorsResult['collection'];
        /**
         * Для админов проекта получаем список блогов и передаем их во вьювер
         */
        if($this->oUserCurrent and $this->oUserCurrent->isAdministrator()) {
            $aBlogs = $this->Blog_GetBlogs();
            unset($aBlogs[$oBlog->getId()]);

            $this->Viewer_Assign('aBlogs',$aBlogs);
        }
        /**
         * Вызов хуков
         */
        $this->Hook_Run('blog_collective_show',array('oBlog'=>$oBlog,'sShowType'=>$sShowType));
        /**
         * Загружаем переменные в шаблон
         */
        $this->Viewer_Assign('aBlogUsers',$aBlogUsers);
        $this->Viewer_Assign('aBlogModerators',$aBlogModerators);
        $this->Viewer_Assign('aBlogAdministrators',$aBlogAdministrators);
        $this->Viewer_Assign('iCountBlogUsers',$aBlogUsersResult['count']);
        $this->Viewer_Assign('iCountBlogModerators',$aBlogModeratorsResult['count']);
        $this->Viewer_Assign('iCountBlogAdministrators',$aBlogAdministratorsResult['count']+1);
        $this->Viewer_Assign('oBlog',$oBlog);
        $this->Viewer_Assign('bCloseBlog',$bCloseBlog);
        /**
         * Устанавливаем title страницы
         */
        $this->Viewer_AddHtmlTitle($oBlog->getTitle());
        $this->Viewer_SetHtmlRssAlternate(Router::GetPath('rss').'blog/'.$oBlog->getUrl().'/',$oBlog->getTitle());
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('blog');
    }
}
?>