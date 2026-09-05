# 全局变量 `$bd`

来源：`app/index/controller/cms/Base.php` 的 `assignBd()`。所有前台模板均可使用 `$bd.xxx`。

## 站点基础

`sitepath`（域名）、`httpurl`（域名）、`pageurl`（当前完整 URL）、`homeurl`（首页 URL）、`sitetplpath`（当前主题资源路径 `/template/cms/<主题>`）、`is_home`（是否首页，`0`/`1`）。

## SEO

`pagetitle`、`pagedescription`、`pagekeywords`——分别来自站点设置的 `sitetitle`、`sitedescription`、`sitekeywords`。

## 搜索与工具

`scaction`（搜索提交地址）、`sitemap`（站点地图 XML 地址）、`checkcode`（验证码图片地址）、`qrcode`（二维码生成地址）。

## 会员

`islogin`（是否已登录，布尔值）、`registerstatus`、`loginstatus`（均默认 `true`）、`register`（注册地址）、`login`（登录地址）。

## 多语言

`sitelanguage`（当前语言代码）、`lgpath`（切换语言提交地址 `/do/area`）。

## 留言与评论

`msgaction`（留言提交地址）、`msgcodestatus`（留言验证码开关）、`commentstatus`（评论功能开关）、`commentaction`（评论提交地址）、`commentcodestatus`（评论验证码开关）。

## 站点信息与公司信息

`assignBd()` 最终将 `$this->site`（站点设置所有字段）和 `$this->company`（公司信息所有字段）合并到 `$bd` 中。模板中可直接使用 `$bd.sitetitle`、`$bd.sitedescription`、`$bd.sitekeywords`、`$bd.companyname`、`$bd.address`、`$bd.phone`、`$bd.email` 等字段。

## 钩子

`BeforeAssignBd` 观察者可以修改或追加全局变量，模块插件可通过此钩子注入自定义 `$bd` 字段。
