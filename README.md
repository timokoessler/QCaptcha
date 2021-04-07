
# QCaptcha


![QCaptcha Preview](https://timokoessler.de/img/qcaptcha-banner.png "QCaptcha Banner")

A major barrier on the Internet for blind people are captchas, as they can neither copy writing from images or select certain images. In addition, most audio captchas are hardly understandable. QCaptcha therefore relies on answering simple questions so that blind people are not locked out.

Unlike other popular captcha solutions, such as Google reCAPTCHA, QCaptcha does not collect and send data to third parties. QCaptcha is fully hosted on your own server, giving you complete control over your users' data.

QCaptcha currently supports German, English and Dutch, but you can add your own language.

![QCaptcha Preview](https://timokoessler.de/img/qcaptcha-preview.png "QCaptcha Preview")

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
require_once "[Path to your files]/qcaptcha/QCaptcha.php";
```
Output the Captcha:
```php
  $captcha = new QCaptcha();
  $captcha->build(); /*Outputs the captcha*/
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

It is possible to add your own languages to the included database. Then you can simply add the language code to the array "qcaptcha_existing_languages".

## Legal notices
The library must store cookies on the device of the user. An Eu directive requires users to be informed about cookies.

## License

This library is under BSD 3-Clause License, have a look to the LICENSE file
