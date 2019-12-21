# tp5 æ•°æ®å­—å…¸
>   æœ¬æ•°æ®å­—å…¸ç”±PHPè„šæœ¬è‡ªåŠ¨å¯¼å‡º,å­—å…¸çš„å¤‡æ³¨æ¥è‡ªæ•°æ®åº“è¡¨åŠå…¶å­—æ®µçš„æ³¨é‡Š(`comment`).å¼€å‘è€…åœ¨å¢æ”¹åº“è¡¨åŠå…¶å­—æ®µæ—¶,è¯·åœ¨ `migration` æ—¶å†™æ˜æ³¨é‡Š,ä»¥å¤‡åæ¥è€…æŸ¥é˜….

## ea_activity  ÏßÏÂ¿Î³Ì

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| title | varchar(255) |  | YES |  | - |
| description | varchar(555) |  | YES |  | ÃèÊö |
| start_time | int(11) |  | YES |  | - |
| end_time | int(11) |  | YES |  | - |
| add_time | int(11) |  | YES |  | - |
| money | int(11) |  | YES |  | - |
| address | varchar(555) |  | YES |  | µØÖ· |
| sort | int(11) |  | YES |  | - |
| status | int(11) | 1 | YES |  | - |

## ea_activity_user  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| uid | int(11) |  | YES |  | - |
| ac_id | int(11) |  | YES |  | - |
| code | varchar(255) |  | YES |  | - |

## ea_addons  ²å¼ş±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | Ö÷¼ü |
| name | varchar(40) |  | NO |  | ²å¼şÃû»ò±êÊ¶ |
| title | varchar(20) |  | NO |  | ÖĞÎÄÃû |
| description | text |  | YES |  | ²å¼şÃèÊö |
| status | tinyint(1) | 1 | NO |  | ×´Ì¬ |
| config | text |  | YES |  | ÅäÖÃ |
| author | varchar(40) |  | YES |  | ×÷Õß |
| version | varchar(20) |  | YES |  | °æ±¾ºÅ |
| create_time | int(10) unsigned | 0 | NO |  | °²×°Ê±¼ä |
| has_adminlist | tinyint(1) unsigned | 0 | NO |  | ÊÇ·ñÓĞºóÌ¨ÁĞ±í |

## ea_admin_user  ¹ÜÀíÔ±±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | smallint(5) unsigned |  | NO | æ˜¯ | - |
| username | varchar(20) |  | NO |  | ¹ÜÀíÔ±ÓÃ»§Ãû |
| password | varchar(50) |  | NO |  | ¹ÜÀíÔ±ÃÜÂë |
| status | tinyint(1) unsigned | 1 | NO |  | ×´Ì¬ 1 ÆôÓÃ 0 ½ûÓÃ |
| create_time | varchar(20) | 0 | YES |  | ×¢²áÊ±¼ä |
| last_login_time | varchar(20) | 0 | YES |  | ×îºóµÇÂ¼Ê±¼ä |
| last_login_ip | varchar(20) |  | YES |  | ×îºóµÇÂ¼IP |
| salt | varchar(20) |  | YES |  | salt |

## ea_auth_group  È¨ÏŞ×é±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | mediumint(8) unsigned |  | NO | æ˜¯ | - |
| title | char(100) |  | NO |  | - |
| status | tinyint(1) | 1 | NO |  | - |
| rules | varchar(255) |  | NO |  | È¨ÏŞ¹æÔòID |

## ea_auth_group_access  È¨ÏŞ×é¹æÔò±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| uid | mediumint(8) unsigned |  | NO |  | - |
| group_id | mediumint(8) unsigned |  | NO |  | - |

## ea_auth_rule  ¹æÔò±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | mediumint(8) unsigned |  | NO | æ˜¯ | - |
| name | varchar(80) |  | NO |  | ¹æÔòÃû³Æ |
| title | varchar(20) |  | NO |  | - |
| type | tinyint(1) unsigned | 1 | NO |  | - |
| status | tinyint(1) | 1 | NO |  | ×´Ì¬ |
| pid | smallint(5) unsigned |  | NO |  | ¸¸¼¶ID |
| icon | varchar(50) |  | YES |  | Í¼±ê |
| sort | tinyint(4) unsigned |  | NO |  | ÅÅĞò |
| condition | char(100) |  | YES |  | - |

## ea_book  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| name | varchar(255) |  | YES |  | - |
| url | varchar(255) |  | YES |  | - |
| cover_img | varchar(255) |  | YES |  | ·âÃæÍ¼Æ¬ |
| description | text |  | YES |  | ¼ò½é |
| book_number | varchar(255) |  | YES |  | ÊéºÅ |
| author | varchar(32) |  | YES |  | ×÷Õß |
| price | int(11) |  | YES |  | ¼Û¸ñ |

## ea_book_curse  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| book_id | int(11) |  | YES |  | - |
| curse_id | int(11) |  | YES |  | - |

## ea_comment  ÆÀÂÛ±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| tid | int(11) |  | NO |  | ÉÏ¼¶ÆÀÂÛ |
| uid | int(11) |  | NO |  | ËùÊô»áÔ± |
| fid | int(11) |  | NO |  | ËùÊôÌû×Ó |
| time | varchar(11) |  | NO |  | Ê±¼ä |
| praise | varchar(11) | 0 | YES |  | ÔŞ |
| reply | varchar(11) | 0 | YES |  | »Ø¸´ |
| content | text |  | NO |  | ÄÚÈİ |

## ea_curse  ÏßÏÂ¿Î³Ì

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| title | varchar(255) |  | YES |  | - |
| description | varchar(555) |  | YES |  | ÃèÊö |
| add_time | int(11) |  | YES |  | - |
| money | int(11) |  | YES |  | - |
| sort | int(11) |  | YES |  | - |
| status | int(11) | 1 | YES |  | - |
| cover_img | varchar(255) |  | YES |  | - |
| author | varchar(32) |  | YES |  | ×÷Õß |
| cate_id | int(11) |  | YES |  | ·ÖÀàid |

## ea_curse_cate  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| title | varchar(255) |  | YES |  | - |
| sort | int(11) |  | YES |  | - |

## ea_curse_chapter  ÕÂ½Ú

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) | 0 | NO |  | - |
| curse_id | int(11) |  | YES |  | - |
| title | varchar(255) |  | YES |  | - |

## ea_curse_sub  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| curse_id | int(11) |  | YES |  | - |
| title | varchar(255) |  | YES |  | - |
| vid | varchar(32) |  | YES |  | - |
| orig_url | varchar(555) |  | YES |  | ²¥·ÅµØÖ· |
| download_url | varchar(555) |  | YES |  | - |

## ea_curse_user  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| uid | int(11) |  | YES |  | - |
| curse_id | int(11) |  | YES |  | - |

## ea_domain  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | - |
| url | varchar(255) |  | NO |  | url |
| num | int(10) |  | NO |  | - |

## ea_file  ÎÄ¼ş±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | ÎÄ¼şID |
| name | varchar(255) |  | NO |  | Ô­Ê¼ÎÄ¼şÃû |
| savename | varchar(255) |  | NO |  | ±£´æÃû³Æ |
| savepath | varchar(255) |  | NO |  | ÎÄ¼ş±£´æÂ·¾¶ |
| ext | char(5) |  | NO |  | ÎÄ¼şºó×º |
| mime | char(40) |  | NO |  | ÎÄ¼şmimeÀàĞÍ |
| size | int(10) unsigned | 0 | NO |  | ÎÄ¼ş´óĞ¡ |
| md5 | varchar(255) |  | NO |  | ÎÄ¼şmd5 |
| sha1 | varchar(255) |  | NO |  | ÎÄ¼ş sha1±àÂë |
| location | tinyint(3) unsigned | 0 | NO |  | ÎÄ¼ş±£´æÎ»ÖÃ |
| create_time | int(10) unsigned |  | NO |  | ÉÏ´«Ê±¼ä |
| download | int(10) unsigned | 0 | NO |  | - |

## ea_forum  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| tid | int(11) |  | NO |  | ÉÏ¼¶ |
| uid | int(11) |  | NO |  | ÓÃ»§ |
| title | varchar(100) |  | NO |  | ±êÌâ |
| open | tinyint(1) | 1 | NO |  | ÏÔÊ¾ |
| choice | tinyint(1) | 0 | NO |  | ¾«Ìù |
| settop | tinyint(1) | 0 | NO |  | ¶¥ÖÃ |
| praise | varchar(11) | 0 | NO |  | ÔŞ |
| view | varchar(11) | 0 | NO |  | ä¯ÀÀÁ¿ |
| time | varchar(11) |  | NO |  | Ê±¼ä |
| reply | varchar(11) | 0 | NO |  | »Ø¸´ |
| keywords | varchar(100) |  | NO |  | ¹Ø¼ü´Ê |
| description | varchar(200) |  | NO |  | ÃèÊö |
| content | text |  | NO |  | ÄÚÈİ |

## ea_forumcate  ÉçÇø·ÖÀà±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| tid | int(11) |  | NO |  | ÉÏ¼¶ |
| name | varchar(32) |  | NO |  | Ãû³Æ |
| type | tinyint(1) | 1 | NO |  | ÀàĞÍ |
| show | tinyint(1) | 1 | NO |  | ÏÔÊ¾ |
| sidebar | tinyint(1) | 1 | NO |  | ²àÀ¸ |
| sort | int(11) | 1 | NO |  | ÅÅĞò |
| pic | varchar(100) |  | NO |  | Í¼Æ¬ |
| time | varchar(32) |  | NO |  | Ê±¼ä |
| keywords | varchar(100) |  | NO |  | ¹Ø¼ü´Ê |
| description | varchar(200) |  | NO |  | ÃèÊö |

## ea_hooks  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | Ö÷¼ü |
| name | varchar(40) |  | NO |  | ¹³×ÓÃû³Æ |
| description | text |  | NO |  | ÃèÊö |
| type | tinyint(1) unsigned | 1 | NO |  | ÀàĞÍ |
| update_time | int(10) unsigned | 0 | NO |  | ¸üĞÂÊ±¼ä |
| addons | varchar(255) |  | NO |  | ¹³×Ó¹ÒÔØµÄ²å¼ş '£¬'·Ö¸î |

## ea_label  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| title | varchar(255) |  | YES |  | - |

## ea_label_with  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| l_id | int(11) |  | YES |  | - |
| f_id | int(11) |  | YES |  | - |
| type | int(11) |  | YES |  | 1 book 2 curse 3 live |

## ea_link  ÓÑÇéÁ´½Ó±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | - |
| name | varchar(20) |  | NO |  | Á´½ÓÃû³Æ |
| link | varchar(255) |  | YES |  | Á´½ÓµØÖ· |
| image | varchar(255) |  | YES |  | Á´½ÓÍ¼Æ¬ |
| status | tinyint(1) unsigned | 1 | NO |  | ×´Ì¬ 1 ÏÔÊ¾  2 Òş²Ø |
| sort | int(10) unsigned | 0 | NO |  | ÅÅĞò |

## ea_live  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| name | varchar(255) |  | YES |  | ±êÌâ |
| cid | varchar(255) |  | YES |  | ÍøÒ×·µ»Øcid |
| push_url | varchar(255) |  | YES |  | ÍÆÁ÷¶Ëurl |
| http_pull_url | varchar(255) |  | YES |  | À­Á÷¶Ë |
| hls_pull_url | varchar(255) |  | YES |  | À­Á÷¶Ë |
| rtmp_pull_url | varchar(255) |  | YES |  | À­Á÷¶Ë |
| teacher_id | int(11) |  | YES |  | ÀÏÊ¦id |
| cover_url | varchar(255) |  | YES |  | - |
| sort | int(11) |  | YES |  | - |
| add_time | int(11) |  | YES |  | - |
| status | int(1) | 1 | YES |  | - |

## ea_log  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | - |
| controller | varchar(255) |  | NO |  | - |
| uid | int(10) unsigned |  | NO |  | - |
| username | varchar(55) |  | NO |  | - |
| add_time | int(10) unsigned |  | NO |  | - |

## ea_message  ÏûÏ¢±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| uid | int(11) |  | NO |  | ËùÊô»áÔ± |
| touid | int(11) | 0 | NO |  | ·¢ËÍ¶ÔÏó |
| type | tinyint(3) | 1 | NO |  | 1ÏµÍ³ÏûÏ¢2Ìû×Ó¶¯Ì¬ |
| content | text |  | NO |  | ÄÚÈİ |
| time | varchar(32) |  | NO |  | Ê±¼ä |
| status | tinyint(1) unsigned | 1 | NO |  | ×´Ì¬ 1 ÏÔÊ¾  2 Òş²Ø |

## ea_nav  µ¼º½±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | - |
| pid | tinyint(3) unsigned |  | NO |  | ¶¥²¿»¹ÊÇµ×²¿ |
| sid | tinyint(3) unsigned |  | NO |  | ÄÚ²¿»¹ÊÇÍâ²¿ |
| name | varchar(20) |  | NO |  | µ¼º½Ãû³Æ |
| alias | varchar(20) |  | YES |  | µ¼º½±ğ³Æ |
| link | varchar(255) |  | YES |  | µ¼º½Á´½Ó |
| icon | varchar(255) |  | YES |  | µ¼º½Í¼±ê |
| target | varchar(10) |  | YES |  | ´ò¿ª·½Ê½ |
| status | tinyint(1) unsigned | 1 | NO |  | ×´Ì¬  0 Òş²Ø  1 ÏÔÊ¾ |
| sort | int(11) | 0 | NO |  | ÅÅĞò |

## ea_point_note  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | - |
| controller | varchar(255) |  | NO |  | - |
| uid | int(10) unsigned |  | NO |  | - |
| pointid | int(10) unsigned |  | NO |  | - |
| score | int(10) |  | NO |  | - |
| add_time | int(10) unsigned |  | NO |  | - |

## ea_readmessage  ÏûÏ¢±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| uid | int(11) |  | NO |  | »áÔ± |
| mid | int(11) | 0 | NO |  | ÏûÏ¢¶ÔÏó |

## ea_slide  ÂÖ²¥Í¼±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | - |
| cid | int(10) unsigned |  | NO |  | ·ÖÀàID |
| name | varchar(50) |  | NO |  | ÂÖ²¥Í¼Ãû³Æ |
| description | varchar(255) |  | YES |  | ËµÃ÷ |
| link | varchar(255) |  | YES |  | Á´½Ó |
| target | varchar(10) |  | YES |  | ´ò¿ª·½Ê½ |
| image | varchar(255) |  | YES |  | ÂÖ²¥Í¼Æ¬ |
| status | tinyint(1) unsigned | 1 | NO |  | ×´Ì¬  1 ÏÔÊ¾  0  Òş²Ø |
| sort | int(10) unsigned | 0 | NO |  | ÅÅĞò |

## ea_slide_category  ÂÖ²¥Í¼·ÖÀà±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | - |
| name | varchar(50) |  | NO |  | ÂÖ²¥Í¼·ÖÀà |

## ea_system  ÏµÍ³ÅäÖÃ±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | - |
| name | varchar(50) |  | NO |  | ÅäÖÃÏîÃû³Æ |
| value | text |  | NO |  | ÅäÖÃÏîÖµ |

## ea_teacher  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| nick_name | varchar(255) |  | YES |  | - |
| real_name | varchar(255) |  | YES |  | - |
| phone | varchar(255) |  | YES |  | - |
| password | varchar(255) |  | YES |  | - |
| salf | varchar(255) |  | YES |  | yab |
| add_time | int(11) |  | YES |  | - |
| status | int(1) |  | YES |  | - |
| open_id | varchar(64) |  | YES |  | - |
| profile | varchar(255) |  | YES |  | ¼ò½é |

## ea_user  

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| nick_name | varchar(255) |  | YES |  | - |
| real_name | varchar(255) |  | YES |  | - |
| phone | varchar(255) |  | YES |  | - |
| password | varchar(255) |  | YES |  | - |
| salt | varchar(255) |  | YES |  | yab |
| add_time | int(11) |  | YES |  | - |
| status | int(1) |  | YES |  | 1 ÆôÓÃ  -1 ½ûÓÃ |
| open_id | varchar(64) |  | YES |  | - |
| profile | varchar(255) |  | YES |  | ¼ò½é |
| accid | varchar(32) |  | YES |  | ÍøÒ×IM id |
| token | varchar(156) |  | YES |  | ÍøÒ×IM ÑéÖ¤ÁîÅÆ |

## ea_usergrade  »áÔ±µÈ¼¶±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| name | varchar(32) |  | NO |  | Ãû³Æ |
| score | int(11) |  | NO |  | µÈ¼¶ËùĞè»ı·Ö |

## ea_video  ÏßÏÂ¿Î³Ì

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | æ˜¯ | - |
| title | varchar(255) |  | YES |  | - |
| duration | int(11) |  | YES |  | Ê±³¤ |
| cover_img | varchar(255) |  | YES |  | ·âÃæ |
| add_time | int(11) |  | YES |  | - |
| money | int(11) |  | YES |  | - |
| sort | int(11) |  | YES |  | - |
| status | int(11) | 1 | YES |  | - |
| view_url | varchar(255) |  | YES |  | ¹Û¿´µØÖ· |
| dl_url | varchar(255) |  | YES |  | ÏÂÔØµØÖ· |
| course_id | int(11) |  | YES |  | ¿Î³Ìid |

## ea_zan  µ¼º½±í

|  å­—æ®µå  |  æ•°æ®ç±»å‹  |  é»˜è®¤å€¼  |  å…è®¸éç©º  |  è‡ªåŠ¨é€’å¢  |  å¤‡æ³¨  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | æ˜¯ | - |
| uid | tinyint(3) unsigned |  | NO |  | ¶¥²¿»¹ÊÇµ×²¿ |
| sid | tinyint(3) unsigned |  | NO |  | ¶Ô·½id»òÕßÌû×Óid»òÕß»Ø¸´µÄid |
| time | varchar(20) | 0 | YES |  | ²Ù×÷Ê±¼ä |
| type | tinyint(1) unsigned | 1 | NO |  | ×´Ì¬  0 ºÃÓÑ  1 Ìû×Ó2 »Ø¸´ÆÀÂÛ |

