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

class PluginDrafts_ActionProfile extends PluginDrafts_Inherit_ActionProfile
{
    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {
        if (Config::Get('plugin.drafts.show_profile')) {
            $this->AddEventPreg('/^.+$/i','/^created/i','/^draft$/i','/^(page([1-9]\d{0,5}))?$/i','EventCreatedDrafts');
        }
        parent::RegisterEvent();
    }

    /**
     * Список черновиков пользователя
     */
    protected function EventCreatedDrafts() {
        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        if (!$this->User_GetUserCurrent() || !$this->User_GetUserCurrent()->isAdministrator()) {
            return parent::EventNotFound();
        }
        $this->sMenuSubItemSelect='draft';
        /**
         * Передан ли номер страницы
         */
        $iPage=$this->GetParamEventMatch(2,2) ? $this->GetParamEventMatch(2,2) : 1;
        /**
         * Получаем список топиков
         */
        $aResult=$this->Topic_GetDraftsPersonalByUser($this->oUserProfile->getId(),$iPage,Config::Get('module.topic.per_page'));
        $aTopics=$aResult['collection'];
        /**
         * Вызов хуков
         */
        $this->Hook_Run('topics_list_show',array('aTopics'=>$aTopics));
        /**
         * Формируем постраничность
         */
        $aPaging=$this->Viewer_MakePaging($aResult['count'],$iPage,Config::Get('module.topic.per_page'),Config::Get('pagination.pages.count'),$this->oUserProfile->getUserWebPath().'created/draft');
        /**
         * Загружаем переменные в шаблон
         */
        $this->Viewer_Assign('aPaging',$aPaging);
        $this->Viewer_Assign('aTopics',$aTopics);
        $this->Viewer_AddHtmlTitle($this->Lang_Get('user_menu_publication').' '.$this->oUserProfile->getLogin());
        $this->Viewer_AddHtmlTitle($this->Lang_Get('user_menu_publication_blog'));
        $this->Viewer_SetHtmlRssAlternate(Router::GetPath('rss').'personal_blog/'.$this->oUserProfile->getLogin().'/',$this->oUserProfile->getLogin());
        /**
         * Устанавливаем шаблон вывода
         */
        $this->SetTemplateAction('created_topics');
    }
    /**
     * Выполняется при завершении работы экшена
     */
    public function EventShutdown() {
        parent::EventShutdown();
        if (!$this->oUserProfile) {
            return ;
        }
        /**
         * Загружаем в шаблон необходимые переменные
         */
        $iCountDraftUser=$this->Topic_GetCountDraftsPersonalByUser($this->oUserProfile->getId());
        $this->Viewer_Assign('iCountDraftUser',$iCountDraftUser);
    }
}
?>