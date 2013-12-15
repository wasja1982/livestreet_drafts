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

class PluginDrafts_HookAddlink extends Hook
{
    public function RegisterHook()
    {
        if ($this->User_IsAuthorization() and $this->User_GetUserCurrent()->isAdministrator()) {
            if (Config::Get('plugin.drafts.show_personal')) {
                $this->AddHook('template_menu_blog_log_item', 'InjectLogLink');
            }
            if (Config::Get('plugin.drafts.show_blog')) {
                $this->AddHook('template_menu_blog_blog_item', 'InjectBlogLink');
            }
            if (Config::Get('plugin.drafts.show_personal') || Config::Get('plugin.drafts.show_blog')) {
                $this->AddHook('template_menu_blog_index_item', 'InjectIndexLink');
            }
            if (Config::Get('plugin.drafts.show_profile')) {
                $this->AddHook('template_menu_profile_created_item', 'InjectProfileLink');
            }
        }
    }

    public function InjectBlogLink($aParam)
    {
        $sTemplatePath = Plugin::GetTemplatePath(__CLASS__) . 'inject_blog_link.tpl';
        if ($this->Viewer_TemplateExists($sTemplatePath)) {
            return $this->Viewer_Fetch($sTemplatePath);
        }
    }

    public function InjectIndexLink($aParam)
    {
        $sTemplatePath = Plugin::GetTemplatePath(__CLASS__) . 'inject_index_link.tpl';
        if ($this->Viewer_TemplateExists($sTemplatePath)) {
            return $this->Viewer_Fetch($sTemplatePath);
        }
    }

    public function InjectLogLink($aParam)
    {
        $sTemplatePath = Plugin::GetTemplatePath(__CLASS__) . 'inject_log_link.tpl';
        if ($this->Viewer_TemplateExists($sTemplatePath)) {
            return $this->Viewer_Fetch($sTemplatePath);
        }
    }

    public function InjectProfileLink($aParam)
    {
        $sTemplatePath = Plugin::GetTemplatePath(__CLASS__) . 'inject_profile_link.tpl';
        if ($this->Viewer_TemplateExists($sTemplatePath)) {
            return $this->Viewer_Fetch($sTemplatePath);
        }
    }
}
?>