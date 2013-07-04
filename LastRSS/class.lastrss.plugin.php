<?php if (!defined('APPLICATION')) exit();

/**
 * Define the plugin:
 */
$PluginInfo['LastRSS'] = array(
	'Name' 		=>	 'Last RSS for Vanilla',
	'Description' => 'Adds the headlines of an RSS feed to the Sidepanel.',
	'Version' 	=>	 '1.5',
	'Author' 	=>	 'Oliver Raduner',
	'AuthorEmail' => 'vanilla@raduner.ch',
	'AuthorUrl' =>	 'http://raduner.ch/',
	'RequiredPlugins' => FALSE,
	'HasLocale' => FALSE,
	'SettingsUrl' => '/plugin/lastrss',
	'SettingsPermission' => 'Garden.Settings.Manage'
);


/**
 * Last RSS Plugin
 *
 * Adds the headlines of an RSS feed to the Sidepanel.
 *
 * @version 1.5
 * @date 07-JAN-2011
 * @author Oliver Raduner <vanilla@raduner.ch>
 * @link http://lastrss.oslab.net/
 *
 * @todo Parsing of multiple Feeds
 */
class LastRssPlugin extends Gdn_Plugin
{
	
	/**
	 * Add a Settings menu to the Admin Dashboard
	 *
	 * @version 1.4
	 * @since 1.0
	 * @author Oliver Raduner <vanilla@raduner.ch>
	 */
	public function Base_GetAppSettingsMenuItems_Handler($Sender) {
		$Menu = $Sender->EventArguments['SideMenu'];
		$Menu->AddLink('Forum', 'Last RSS', 'plugin/lastrss', 'Garden.Settings.Manage');
	}
	
	/**
	 * Last RSS-Plugin Settings Page caller
	 *
	 * @version 1.4
	 * @since 1.0
	 * @author Oliver Raduner <vanilla@raduner.ch>
	 */
	public function PluginController_LastRSS_Create($Sender, $Args = array())
	{
		$Sender->Permission('Garden.Settings.Manage');
		$Sender->Form = new Gdn_Form();
		$Validation = new Gdn_Validation();
		$ConfigurationModel = new Gdn_ConfigurationModel($Validation);
		$ConfigurationModel->SetField(array('Plugins.LastRSS.FeedTitle', 'Plugins.LastRSS.FeedURL'));
		$Sender->Form->SetModel($ConfigurationModel);
		
		if ($Sender->Form->AuthenticatedPostBack() === FALSE)
		{
			$Sender->Form->SetData($ConfigurationModel->Data);
		} else {
			$Data = $Sender->Form->FormValues();
			
			if ($Sender->Form->Save() !== FALSE) {
				$Sender->StatusMessage = T("Your settings have been saved.");
			}
		}

		$Sender->AddSideMenu('plugin/lastrss');		
		$Sender->SetData('Title', 'Last RSS');
		$Sender->Render($this->GetView('lastrss.php'));
	}
		
	
	/**
	 * Hack the basic rendering in order to add the RSS panel
	 * 
	 * @version 1.5
	 * @since 1.0
	 * @author Oliver Raduner <vanilla@raduner.ch>
	 */
	public function Base_Render_Before($Sender)
	{
		// Continue Last RSS Plugin only on the desired pages...
		$DisplayOn =  array('activitycontroller', 'discussionscontroller'); // Pages where the Feed should be displayed on
		if (!InArrayI($Sender->ControllerName, $DisplayOn)) return;
		
		
		// Include the lastRSS Class
		include('plugins' . DS . 'LastRSS' . DS . 'vendors' . DS . 'lastRSS.class.php');
		
		// Initialize the Class & Variables
		$Rss = new lastRSS;
		$HtmlOut = '';
		
		/**
		 * CUSTOM SETTINGS
		 */
		$RssFeedTitle 		=  C('Plugins.LastRSS.FeedTitle'); // Optional, custom Title for the Feed
		$RssFeedUrl			=  C('Plugins.LastRSS.FeedURL'); // Feed Address
		$Rss->cache_dir		=  PATH_CACHE;
		$Rss->cache_time	=  3600;  // Cache Feed for 1 hour
		$Rss->items_limit	=  10;	  // Amount of items to fetch
		$Rss->stripHTML		=  FALSE; // Remove HTML from Feeds
		
		// Load the RSS file
		if ($rs = $Rss->Get($RssFeedUrl))
		{
			$HtmlOut .= '<div id="RSSFeed" class="Box">';
				$HtmlOut .= '<h4><a href="'.$rs['link'].'">';
				$HtmlOut .= ($RssFeedTitle != '') ? imap_utf8($RssFeedTitle) : imap_utf8($rs['title']);
				$HtmlOut .= '</a></h4>';
				$HtmlOut .= '<ul class="PanelActivity">';
				
				for($i=0; $i<$Rss->items_limit; $i++)
				{
					$HtmlOut .= '<li class="FeedEntry"><a href="'.$rs['items'][$i]['link'].'">'.imap_utf8($rs['items'][$i]['title']).'</a><br />'.Gdn_Format::Date(strtotime($rs['items'][$i]['pubDate'])).'</li>';
         			// imap_utf8 should ensure the proper output of the Feed-Title in UTF8
         		}
         		$HtmlOut .= '</ul>';
			$HtmlOut .= '</div>';
			
			$Sender->AddAsset('Panel', $HtmlOut, 'RSSFeed');
			
		} else {
			
			$Sender->AddAsset('Panel', T('Error: RSS feed not found!'), 'RSSFeed');
			
		}
	}
	
	
	/**
	 * Set default values
	 *
	 * @version 1.1
	 * @since 1.0
	 */
	public function Setup() {
		SaveToConfig('Plugins.LastRSS.FeedTitle', 'Vanilla Forums Blog');
		SaveToConfig('Plugins.LastRSS.FeedURL', 'http://feeds.feedburner.com/vanillaforums');
		//SaveToConfig('Plugins.LastRSS.DisplayOn', array('activitycontroller','discussionscontroller'));
	}
	
	/**
	 * On Plugin deactivation, remove Last RSS Settings from the Config file
	 *
	 * @version 1.4
	 * @since 1.0
	 */
	public function OnDisable()
	{
		RemoveFromConfig('Plugins.LastRSS.FeedTitle');
		RemoveFromConfig('Plugins.LastRSS.FeedURL');
	}
	
}

?>