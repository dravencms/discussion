# Dravencms Discussion module

This is a simple Discussion module for dravencms

## Instalation

The best way to install dravencms/discussion is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/discussion:@dev
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	discussion: Dravencms\Discussion\DI\DiscussionExtension
```
