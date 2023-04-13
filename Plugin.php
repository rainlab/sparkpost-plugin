<?php namespace RainLab\SparkPost;

use Event;
use Config;
use System\Classes\PluginBase;

/**
 * Plugin Information File
 *
 * @link https://docs.octobercms.com/3.x/extend/system/plugins.html
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'SparkPost',
            'description' => 'Adds support for SparkPost as a mail driver.',
            'author' => 'Alexey Bobkov, Samuel Georges',
            'icon' => 'icon-envelope-square',
            'homepage' => 'https://github.com/rainlab/sparkpost-plugin',
        ];
    }

    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        // Adds SparkPost as an available mailing method
        Event::listen('system.mail.getSendModeOptions', function(&$options) {
            $options['sparkpost'] = 'SparkPost';
        });

        // Adds the "secret" form input, show when SparkPost is selected
        Event::listen('backend.form.extendFields', function($form) {
            if (
                !$form->getController() instanceof \System\Controllers\Settings ||
                !$form->getModel() instanceof \System\Models\MailSetting
            ) {
                return;
            }

            $form->addTabField('sparkpost_secret', "SparkPost Secret")
                ->displayAs('sensitive')
                ->commentAbove("Enter your SparkPost API secret key")
                ->tab("General")
                ->trigger([
                    'action' => 'show',
                    'field' => 'send_mode',
                    'condition' => 'value[sparkpost]'
                ]);
        });

        // Sets services configuration (config/services.php) for SparkPost
        Event::listen('system.mail.applyConfigValues', function($settings) {
            if ($settings->send_mode === 'sparkpost') {
                Config::set('services.sparkpost.secret', $settings->sparkpost_secret);
            }
        });
    }
}
