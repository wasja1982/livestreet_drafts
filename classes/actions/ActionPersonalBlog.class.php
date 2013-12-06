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
}
?>
