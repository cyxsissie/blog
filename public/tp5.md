# tp5 数据字典
>   本数据字典由PHP脚本自动导出,字典的备注来自数据库表及其字段的注释(`comment`).开发者在增改库表及其字段时,请在 `migration` 时写明注释,以备后来者查阅.

## ea_activity  ���¿γ�

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| title | varchar(255) |  | YES |  | - |
| description | varchar(555) |  | YES |  | ���� |
| start_time | int(11) |  | YES |  | - |
| end_time | int(11) |  | YES |  | - |
| add_time | int(11) |  | YES |  | - |
| money | int(11) |  | YES |  | - |
| address | varchar(555) |  | YES |  | ��ַ |
| sort | int(11) |  | YES |  | - |
| status | int(11) | 1 | YES |  | - |

## ea_activity_user  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| uid | int(11) |  | YES |  | - |
| ac_id | int(11) |  | YES |  | - |
| code | varchar(255) |  | YES |  | - |

## ea_addons  �����

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | ���� |
| name | varchar(40) |  | NO |  | ��������ʶ |
| title | varchar(20) |  | NO |  | ������ |
| description | text |  | YES |  | ������� |
| status | tinyint(1) | 1 | NO |  | ״̬ |
| config | text |  | YES |  | ���� |
| author | varchar(40) |  | YES |  | ���� |
| version | varchar(20) |  | YES |  | �汾�� |
| create_time | int(10) unsigned | 0 | NO |  | ��װʱ�� |
| has_adminlist | tinyint(1) unsigned | 0 | NO |  | �Ƿ��к�̨�б� |

## ea_admin_user  ����Ա��

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | smallint(5) unsigned |  | NO | 是 | - |
| username | varchar(20) |  | NO |  | ����Ա�û��� |
| password | varchar(50) |  | NO |  | ����Ա���� |
| status | tinyint(1) unsigned | 1 | NO |  | ״̬ 1 ���� 0 ���� |
| create_time | varchar(20) | 0 | YES |  | ע��ʱ�� |
| last_login_time | varchar(20) | 0 | YES |  | ����¼ʱ�� |
| last_login_ip | varchar(20) |  | YES |  | ����¼IP |
| salt | varchar(20) |  | YES |  | salt |

## ea_auth_group  Ȩ�����

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | mediumint(8) unsigned |  | NO | 是 | - |
| title | char(100) |  | NO |  | - |
| status | tinyint(1) | 1 | NO |  | - |
| rules | varchar(255) |  | NO |  | Ȩ�޹���ID |

## ea_auth_group_access  Ȩ��������

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| uid | mediumint(8) unsigned |  | NO |  | - |
| group_id | mediumint(8) unsigned |  | NO |  | - |

## ea_auth_rule  �����

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | mediumint(8) unsigned |  | NO | 是 | - |
| name | varchar(80) |  | NO |  | �������� |
| title | varchar(20) |  | NO |  | - |
| type | tinyint(1) unsigned | 1 | NO |  | - |
| status | tinyint(1) | 1 | NO |  | ״̬ |
| pid | smallint(5) unsigned |  | NO |  | ����ID |
| icon | varchar(50) |  | YES |  | ͼ�� |
| sort | tinyint(4) unsigned |  | NO |  | ���� |
| condition | char(100) |  | YES |  | - |

## ea_book  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| name | varchar(255) |  | YES |  | - |
| url | varchar(255) |  | YES |  | - |
| cover_img | varchar(255) |  | YES |  | ����ͼƬ |
| description | text |  | YES |  | ��� |
| book_number | varchar(255) |  | YES |  | ��� |
| author | varchar(32) |  | YES |  | ���� |
| price | int(11) |  | YES |  | �۸� |

## ea_book_curse  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| book_id | int(11) |  | YES |  | - |
| curse_id | int(11) |  | YES |  | - |

## ea_comment  ���۱�

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| tid | int(11) |  | NO |  | �ϼ����� |
| uid | int(11) |  | NO |  | ������Ա |
| fid | int(11) |  | NO |  | �������� |
| time | varchar(11) |  | NO |  | ʱ�� |
| praise | varchar(11) | 0 | YES |  | �� |
| reply | varchar(11) | 0 | YES |  | �ظ� |
| content | text |  | NO |  | ���� |

## ea_curse  ���¿γ�

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| title | varchar(255) |  | YES |  | - |
| description | varchar(555) |  | YES |  | ���� |
| add_time | int(11) |  | YES |  | - |
| money | int(11) |  | YES |  | - |
| sort | int(11) |  | YES |  | - |
| status | int(11) | 1 | YES |  | - |
| cover_img | varchar(255) |  | YES |  | - |
| author | varchar(32) |  | YES |  | ���� |
| cate_id | int(11) |  | YES |  | ����id |

## ea_curse_cate  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| title | varchar(255) |  | YES |  | - |
| sort | int(11) |  | YES |  | - |

## ea_curse_chapter  �½�

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) | 0 | NO |  | - |
| curse_id | int(11) |  | YES |  | - |
| title | varchar(255) |  | YES |  | - |

## ea_curse_sub  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| curse_id | int(11) |  | YES |  | - |
| title | varchar(255) |  | YES |  | - |
| vid | varchar(32) |  | YES |  | - |
| orig_url | varchar(555) |  | YES |  | ���ŵ�ַ |
| download_url | varchar(555) |  | YES |  | - |

## ea_curse_user  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| uid | int(11) |  | YES |  | - |
| curse_id | int(11) |  | YES |  | - |

## ea_domain  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | - |
| url | varchar(255) |  | NO |  | url |
| num | int(10) |  | NO |  | - |

## ea_file  �ļ���

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | �ļ�ID |
| name | varchar(255) |  | NO |  | ԭʼ�ļ��� |
| savename | varchar(255) |  | NO |  | �������� |
| savepath | varchar(255) |  | NO |  | �ļ�����·�� |
| ext | char(5) |  | NO |  | �ļ���׺ |
| mime | char(40) |  | NO |  | �ļ�mime���� |
| size | int(10) unsigned | 0 | NO |  | �ļ���С |
| md5 | varchar(255) |  | NO |  | �ļ�md5 |
| sha1 | varchar(255) |  | NO |  | �ļ� sha1���� |
| location | tinyint(3) unsigned | 0 | NO |  | �ļ�����λ�� |
| create_time | int(10) unsigned |  | NO |  | �ϴ�ʱ�� |
| download | int(10) unsigned | 0 | NO |  | - |

## ea_forum  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| tid | int(11) |  | NO |  | �ϼ� |
| uid | int(11) |  | NO |  | �û� |
| title | varchar(100) |  | NO |  | ���� |
| open | tinyint(1) | 1 | NO |  | ��ʾ |
| choice | tinyint(1) | 0 | NO |  | ���� |
| settop | tinyint(1) | 0 | NO |  | ���� |
| praise | varchar(11) | 0 | NO |  | �� |
| view | varchar(11) | 0 | NO |  | ����� |
| time | varchar(11) |  | NO |  | ʱ�� |
| reply | varchar(11) | 0 | NO |  | �ظ� |
| keywords | varchar(100) |  | NO |  | �ؼ��� |
| description | varchar(200) |  | NO |  | ���� |
| content | text |  | NO |  | ���� |

## ea_forumcate  ���������

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| tid | int(11) |  | NO |  | �ϼ� |
| name | varchar(32) |  | NO |  | ���� |
| type | tinyint(1) | 1 | NO |  | ���� |
| show | tinyint(1) | 1 | NO |  | ��ʾ |
| sidebar | tinyint(1) | 1 | NO |  | ���� |
| sort | int(11) | 1 | NO |  | ���� |
| pic | varchar(100) |  | NO |  | ͼƬ |
| time | varchar(32) |  | NO |  | ʱ�� |
| keywords | varchar(100) |  | NO |  | �ؼ��� |
| description | varchar(200) |  | NO |  | ���� |

## ea_hooks  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | ���� |
| name | varchar(40) |  | NO |  | �������� |
| description | text |  | NO |  | ���� |
| type | tinyint(1) unsigned | 1 | NO |  | ���� |
| update_time | int(10) unsigned | 0 | NO |  | ����ʱ�� |
| addons | varchar(255) |  | NO |  | ���ӹ��صĲ�� '��'�ָ� |

## ea_label  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| title | varchar(255) |  | YES |  | - |

## ea_label_with  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| l_id | int(11) |  | YES |  | - |
| f_id | int(11) |  | YES |  | - |
| type | int(11) |  | YES |  | 1 book 2 curse 3 live |

## ea_link  �������ӱ�

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | - |
| name | varchar(20) |  | NO |  | �������� |
| link | varchar(255) |  | YES |  | ���ӵ�ַ |
| image | varchar(255) |  | YES |  | ����ͼƬ |
| status | tinyint(1) unsigned | 1 | NO |  | ״̬ 1 ��ʾ  2 ���� |
| sort | int(10) unsigned | 0 | NO |  | ���� |

## ea_live  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| name | varchar(255) |  | YES |  | ���� |
| cid | varchar(255) |  | YES |  | ���׷���cid |
| push_url | varchar(255) |  | YES |  | ������url |
| http_pull_url | varchar(255) |  | YES |  | ������ |
| hls_pull_url | varchar(255) |  | YES |  | ������ |
| rtmp_pull_url | varchar(255) |  | YES |  | ������ |
| teacher_id | int(11) |  | YES |  | ��ʦid |
| cover_url | varchar(255) |  | YES |  | - |
| sort | int(11) |  | YES |  | - |
| add_time | int(11) |  | YES |  | - |
| status | int(1) | 1 | YES |  | - |

## ea_log  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | - |
| controller | varchar(255) |  | NO |  | - |
| uid | int(10) unsigned |  | NO |  | - |
| username | varchar(55) |  | NO |  | - |
| add_time | int(10) unsigned |  | NO |  | - |

## ea_message  ��Ϣ��

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| uid | int(11) |  | NO |  | ������Ա |
| touid | int(11) | 0 | NO |  | ���Ͷ��� |
| type | tinyint(3) | 1 | NO |  | 1ϵͳ��Ϣ2���Ӷ�̬ |
| content | text |  | NO |  | ���� |
| time | varchar(32) |  | NO |  | ʱ�� |
| status | tinyint(1) unsigned | 1 | NO |  | ״̬ 1 ��ʾ  2 ���� |

## ea_nav  ������

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | - |
| pid | tinyint(3) unsigned |  | NO |  | �������ǵײ� |
| sid | tinyint(3) unsigned |  | NO |  | �ڲ������ⲿ |
| name | varchar(20) |  | NO |  | �������� |
| alias | varchar(20) |  | YES |  | ������� |
| link | varchar(255) |  | YES |  | �������� |
| icon | varchar(255) |  | YES |  | ����ͼ�� |
| target | varchar(10) |  | YES |  | �򿪷�ʽ |
| status | tinyint(1) unsigned | 1 | NO |  | ״̬  0 ����  1 ��ʾ |
| sort | int(11) | 0 | NO |  | ���� |

## ea_point_note  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | - |
| controller | varchar(255) |  | NO |  | - |
| uid | int(10) unsigned |  | NO |  | - |
| pointid | int(10) unsigned |  | NO |  | - |
| score | int(10) |  | NO |  | - |
| add_time | int(10) unsigned |  | NO |  | - |

## ea_readmessage  ��Ϣ��

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| uid | int(11) |  | NO |  | ��Ա |
| mid | int(11) | 0 | NO |  | ��Ϣ���� |

## ea_slide  �ֲ�ͼ��

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | - |
| cid | int(10) unsigned |  | NO |  | ����ID |
| name | varchar(50) |  | NO |  | �ֲ�ͼ���� |
| description | varchar(255) |  | YES |  | ˵�� |
| link | varchar(255) |  | YES |  | ���� |
| target | varchar(10) |  | YES |  | �򿪷�ʽ |
| image | varchar(255) |  | YES |  | �ֲ�ͼƬ |
| status | tinyint(1) unsigned | 1 | NO |  | ״̬  1 ��ʾ  0  ���� |
| sort | int(10) unsigned | 0 | NO |  | ���� |

## ea_slide_category  �ֲ�ͼ�����

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | - |
| name | varchar(50) |  | NO |  | �ֲ�ͼ���� |

## ea_system  ϵͳ���ñ�

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | - |
| name | varchar(50) |  | NO |  | ���������� |
| value | text |  | NO |  | ������ֵ |

## ea_teacher  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| nick_name | varchar(255) |  | YES |  | - |
| real_name | varchar(255) |  | YES |  | - |
| phone | varchar(255) |  | YES |  | - |
| password | varchar(255) |  | YES |  | - |
| salf | varchar(255) |  | YES |  | yab |
| add_time | int(11) |  | YES |  | - |
| status | int(1) |  | YES |  | - |
| open_id | varchar(64) |  | YES |  | - |
| profile | varchar(255) |  | YES |  | ��� |

## ea_user  

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| nick_name | varchar(255) |  | YES |  | - |
| real_name | varchar(255) |  | YES |  | - |
| phone | varchar(255) |  | YES |  | - |
| password | varchar(255) |  | YES |  | - |
| salt | varchar(255) |  | YES |  | yab |
| add_time | int(11) |  | YES |  | - |
| status | int(1) |  | YES |  | 1 ����  -1 ���� |
| open_id | varchar(64) |  | YES |  | - |
| profile | varchar(255) |  | YES |  | ��� |
| accid | varchar(32) |  | YES |  | ����IM id |
| token | varchar(156) |  | YES |  | ����IM ��֤���� |

## ea_usergrade  ��Ա�ȼ���

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| name | varchar(32) |  | NO |  | ���� |
| score | int(11) |  | NO |  | �ȼ�������� |

## ea_video  ���¿γ�

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(11) |  | NO | 是 | - |
| title | varchar(255) |  | YES |  | - |
| duration | int(11) |  | YES |  | ʱ�� |
| cover_img | varchar(255) |  | YES |  | ���� |
| add_time | int(11) |  | YES |  | - |
| money | int(11) |  | YES |  | - |
| sort | int(11) |  | YES |  | - |
| status | int(11) | 1 | YES |  | - |
| view_url | varchar(255) |  | YES |  | �ۿ���ַ |
| dl_url | varchar(255) |  | YES |  | ���ص�ַ |
| course_id | int(11) |  | YES |  | �γ�id |

## ea_zan  ������

|  字段名  |  数据类型  |  默认值  |  允许非空  |  自动递增  |  备注  |
| ------ | ------ | ------ | ------ | ------ | ------ |
| id | int(10) unsigned |  | NO | 是 | - |
| uid | tinyint(3) unsigned |  | NO |  | �������ǵײ� |
| sid | tinyint(3) unsigned |  | NO |  | �Է�id��������id���߻ظ���id |
| time | varchar(20) | 0 | YES |  | ����ʱ�� |
| type | tinyint(1) unsigned | 1 | NO |  | ״̬  0 ����  1 ����2 �ظ����� |

