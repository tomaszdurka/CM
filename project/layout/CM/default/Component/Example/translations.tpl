<h2>Translation</h2>
<h3>Heading Letter Case</h3>
<p>(for Headings, Menus, Tooltips, Buttons,...)</p>
<p>First and last word, as well as all open class words capitalized:</p>
<ul class="defaultList">
	<li>Nouns</li>
	<li>
		Main verbs (not auxiliary verbs:
		be (am, are, is, was, were, being), can, could, do (did, does, doing), have (had, has, having), may, might, must, shall, should, will, would)
	</li>
	<li>Adjectives</li>
	<li>Adverbs</li>
	<li>Interjections</li>
</ul>
<h4>Examples</h4>
<ul class="defaultList">
	<li>The Vitamins are in my Fresh California Raisins.</li>
	<li>Terms of Use</li>
</ul>

<h3>Add Translation</h3>
<p>Use this translation method for words, short reusable sentences.</p>
{code}{literal}{{/literal}translate 'Some cool phrase'}{/code}
{code}{literal}{{/literal}translate 'Some cool phrase with {literal}{$variable}{/literal}'}{/code}

<h3>Add Key Translation</h3>
<p>Use this translation method for internal generated words, long texts, unique sentences.</p>
{code}{ldelim}translate '.language.key'}{/code}
{code}<?php
$langauge = CM_Model_Language::findByAbbreviation('en');
$language->setTranslation('.language.key', 'Transalation without variables');
{/code}

<h3>Add Translation with Variables</h3>
{code}{ldelim}translate '.language.key' variable=$variable}{/code}


{code}
{literal}<?php
$langauge = CM_Model_Language::findByAbbreviation('en');
$language->setTranslation('.language.key', 'Transalation with {$variable}', array('variable'));
{/literal}
{/code}

<h3>Delete Translation</h3>
{code}<?php
CM_Model_Language::deleteKey('Some cool phrase');
CM_Model_Language::deleteKey('Some cool phrase with {literal}{$variable}{/literal}');
CM_Model_Language::deleteKey('.language.key');
{/code}