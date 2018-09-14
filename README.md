# Sprout Invisible Captcha

Invisible Captcha provides several user-friendly methods to protect your forms from vile spammers and evil robots.

Invisible Captcha provides several user-friendly methods to protect your forms from vile spammers and evil robots.

## Usage

Select one or many Invisible Captcha security checks to protect your forms from spam.  Invisible Captcha supports:

- [Sprout Forms](http://sprout.barrelstrengthdesign.com/craft-plugins/forms)
- [Contact Form](https://github.com/pixelandtonic/ContactForm)
- [Guest Entries](https://github.com/pixelandtonic/GuestEntries)

Add the captcha between your `<form>` tags with this one line:

``` twig
{{ craft.sproutInvisibleCaptcha.protect() }}
```

By default, if a submission is caught, it will be redirected to your 'redirect' location.  If you want more control, you can set another hidden variable called 'redirectOnFailure'

``` html
<input type="hidden" name="redirectOnFailure" value="somewhere-else">
```

## Invisible Captcha Methods Available

**Prevent duplicate submissions if a user hits submit more than once** (Duplicate Submission Method)

_Explanation_<br>
Sometimes a user may accidentally (or intentially) trigger a form submit button more than once. The Duplicate Submission spam protection method uses a randomly generated unique id to verify that a form is only submitted once to the database.

_How do I test this method?_<br>
When you submit your form, hit the submit button as many times as you can as fast as you can (or at least twice)! With this setting turned off, if you are not preventing duplicated submissions in any other way on the front-end of your website, you should see multiple form submissions in your database. With this setting turned on, you should only see one form entry get saved to the database. Blocked submissions will be logged in the database and can also be seen in the Invisible Captcha logs.

<hr>

**Prevent a form from being submmitted if a user does not have JavaScript enabled** (Javascript Method)

_Explanation_<br>
Most human users visiting your website have Javascript enabled in their web browser. Often, when robots access your website programatically, they do not have Javascript enabled. The Javascript spam protection method tests if a user submitting your form has Javascript enabled in their browser and rejects the submission if they do not.

_Note: While this method can be very effective at stopping spam and is frequently used, there is a small chance some real users will not be able to submit your form if they accessing your website from a location or device where Javascript is disabled. Check your website analytics to make the best decision for your audience._

_How do I test this method?_<br>
Disable javascript in your web browser. Refresh your form page and submit the form as usual. With javascript disabled in your web browser your form submission should be blocked. Blocked submissions will be logged in the database and can also be seen in the Invisible Captcha logs.

<hr>

**Block form submissions by robots who auto-fill all of your form fields** (Honeypot Method)
  
_Explanation_<br>
Many robots fill out every single form field before they submit. The honeypot method of spam prevention creates a hidden field that should not be filled in by a user on your site because they will never see the field or know it exists. When a robot automatically fills in the field and submits the form, the form submission will be denied.

_Note: Some screen readers will see this hidden field. We will clearly label the hidden field with a message that says to a user with a screen reader to not add anything to the honeypot form field._

_How do I test this method?_<br>
To test this method you will need to modify the HTML on your form page before you submit it. Open your browser tools and find the HTML ⟨input⟩ field for your honeypot. It will be within a ⟨div⟩ that uses the name you defined in your settings and the ⟨input⟩ field will also use that same name.

Edit that input field (** This is important. In Chrome for example, if you double click on the input in your browser tools you are not editing your page as HTML and you must right click and select "Edit as HTML" to be sure that you are actually modifying the page code so that it is submitted differently for your test). Add any string of characters to the value="" parameter so that it is not blank (i.e. value="bees!") and then submit your form. Blocked submissions will be logged in the database and can also be seen in the Invisible Captcha logs.

<hr>

**Require minimum time to fill out your form** (Time-based Method)

_Explanation_<br>
The Time-based spam prevention method protects against robots who submit forms quicker than humans could.

_Note: If a human does fill out the form too quickly, it will be blocked as spam, so be sure to set this number to a reasonable amount of seconds. 4 or 5 seconds is usually enough to weed out the majority of spam bots, while letting actual humans submit forms successfully._

_Settings_<br>
Minimum time to submit form<br>
_This is the minimum time in seconds a user should take to fill out all form fields and hit submit._

_How do I test this method?_<br>
To test the time-based method, update your time setting to something absurdly high. For example, set the minumum time required to submit your form to 1 day: 86400. Now go and submit your form in less than a day (if you can)! Blocked submissions will be logged in the database and can also be seen in the Invisible Captcha logs.

## Logging Failed Submissions

You can log failed submissions in the database to learn about what types of attacks your web forms are experiencing. Logs work but can only be accessed via the database right now.

## Sprout Forms Integration

Sprout Invisible Captcha works with Sprout Forms.  

**Enable Sprout Forms Protection**<br>
Select the `Enable Sprout Forms Protection` setting to dynamically output your Invisible Captcha when you're using the `{{ craft.sproutForms.displayForm() }}` tag.

## Requirements

* Craft 1.3 or a more recent version of Craft 2

### Installation

* Place the `sproutinvisiblecaptcha` folder inside your `craft/plugins` folder.
* Install the plugin via the Craft Dashboard. (Settings&rarr;Plugins)

### Updating

* Place the `sproutinvisiblecaptcha` folder inside your `craft/plugins` folder and overwrite the existing copy of `sproutinvisiblecaptcha`.
* Point your browser to your Craft control panel. If you are prompted to proceed with a database update, click “Finish up” and let the database updates run.  If no database updates are needed, you will see your control panel load as normal and you are good to go.

## Support

Via Craft Stack Exchange: Tag your questions with `plugin-sproutinvisiblecaptcha`:
https://craftcms.stackexchange.com/

Via Email:
Send us a note at: sprout@barrelstrengthdesign.com