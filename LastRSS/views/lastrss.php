<?php if (!defined('APPLICATION')) exit();

echo Wrap($this->Data('Title'), 'h1');

echo $this->Form->Open();
echo $this->Form->Errors();
?>
<div class="Info">
   <?php echo T('Add here a valid RSS-Feed URL to be displayed in the Sidepanel:'); ?>
</div>
<div>
	<ul><li><?php
	echo $this->Form->Label(T('Title'), 'Plugins.LastRSS.FeedTitle');
	echo $this->Form->TextBox('Plugins.LastRSS.FeedTitle', array('placeholder' => 'Vanilla Forums Blog'));
	?></li>
	<li><?php
	echo $this->Form->Label(T('Address'), 'Plugins.LastRSS.FeedURL');
	echo $this->Form->TextBox('Plugins.LastRSS.FeedURL', array('placeholder' => 'http://feeds.feedburner.com/vanillaforums'));
	/*?></li>
	<li><?php
	echo $this->Form->Label('Display on Pages', 'Plugins.LastRSS.DisplayOn');
	echo $this->Form->CheckBoxList('Plugins.LastRSS.DisplayOn', array('Dashboard', 'Activities', 'Discussions'), array('dashboardcontroller', 'activitycontroller', 'discussionscontroller'));
	*/?></li>
	<li><?php
	echo $this->Form->Close('Save');
	?></li></ul>
</div>