# QCaptcha


![QCaptcha Preview](https://timokoessler.de/qcaptcha/img/git-banner.png "QCaptcha Preview")

A major barrier on the Internet for blind people are captchas, as they can neither copy writing from images or select certain images. In addition, most audio captchas are hardly understandable. QCaptcha therefore relies on answering simple questions so that blind people are not locked out.

Unlike other popular captcha solutions, such as Google reCAPTCHA, QCaptcha does not collect and send data to third parties. QCaptcha is fully hosted on your own server, giving you complete control over your users' data.

QCaptcha currently supports German, English and Dutch.

![QCaptcha Preview](https://timokoessler.de/qcaptcha/img/preview.png "QCaptcha Preview")

[Go to the demo](https://timokoessler.de/qcaptcha/php/ "QCaptcha Demo")

# Installation
## Requirements

- PHP Version 7.1 or higher
- PHP Extension SQLite3

Downloads can be found in the "Release" tab.

A WordPress plugin is also available: [Learn More](https://wordpress.org/plugins/qcaptcha/ "QCaptcha WP-Plugin")
## Usage
First add the stylesheet to your page:
```html
<link rel="stylesheet" href="[Path to your files]/qcaptcha/css/qcaptcha.min.css">
```
After this add the PHP-Library:
```php
include_once "[Path to your files]/qcaptcha/QCaptcha.php";
```
Output the Captcha:
```php
  $captcha = new QCaptcha();
  $captcha->build(); /*Output the captcha*/
```
Validate the captcha:
```php
if($captcha->isValid()){ //Checks $_POST['captcha']
  //Captcha valid
} else {
  //Captcha invalid
}
```
## Advanced

You can found the instructions how to add your own questions or languages to the captcha [here.](https://timokoessler.de/qcaptcha/docs/advanced "QCaptcha advanced instructions")

If you want to support the development of the plugin, I would be very happy if you would send your own questions to me so that I can expand the database for everyone. This also applies, of course, to languages. 

## Legal notices
The library must store cookies on the device of the user. An Eu directive requires users to be informed about cookies.

## License

This library is under BSD 3-Clause License, have a look to the LICENSE file
