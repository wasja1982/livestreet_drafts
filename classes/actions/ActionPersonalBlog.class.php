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

class PluginDrafts_ActionPersonalBlog extends PluginDrafts_Inherit_ActionPersonalBlog
{
    /**
     * Регистрируем необходимые евенты
     *
     */
    protected function RegisterEvent() {
        if (Config::Get('plugin.drafts.show_personal')) {
            $this->AddEventPreg('/^draft$/i','/^(page([1-9]\d{0,5}))?$/i','EventTopics');
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
}
?>
