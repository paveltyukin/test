define([
	"dijit/layout/BorderContainer",
	"dijit/layout/ContentPane",
	"dijit/Dialog",
	"dijit/form/Button",
	"dijit/form/Form", 
	"dijit/form/Select",
	"dijit/form/DateTextBox", 
	"dijit/form/TextBox", 
	"dijit/form/Textarea", 
	"dijit/Editor",
	"dijit/form/CheckBox"
], function(BorderContainer,ContentPane,Dialog,Button,Form,Select,DateTextBox,TextBox,Textarea,Editor,CheckBox) {
	return {
    BorderContainer:BorderContainer,
    ContentPane:ContentPane,
    Dialog:Dialog,
    Button:Button,
    Form:Form,
    Select:Select,
    DateTextBox:DateTextBox,
    TextBox:TextBox,
    Textarea:Textarea,
    Editor:Editor,
    CheckBox:CheckBox
	}
});