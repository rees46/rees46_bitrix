<?php

namespace Rees46;

class Options
{
	public static function getRecommenderCSS()
	{
		return \COption::GetOptionString(\mk_rees46::MODULE_ID, 'css', <<<'CSS'

.rees46-recommend {
    font-family: Arial, sans-serif;
    margin-bottom: 20px;
    display: table;
    font-size: 13px;
}
    .rees46-recommend .recommender-block-title {
        font-size: 20px;
        font-weight: bold;
        text-transform: uppercase;
        color: #212322;
        margin-bottom: 12px;
    }
    .rees46-recommend .recommended-items {
        overflow: hidden;
        height: 320px;
    }
        .rees46-recommend .recommended-items .recommended-item {
            width: 182px;
            padding: 20px 14px 20px 14px;
            display: block;
            float: left;
            margin: 0 14px 20px 0;
            border-radius: 4px;
            border: 1px solid #e3e3dc;
            height: 260px;
            text-align: center;
            box-shadow: 0 3px 2px rgba(0,0,0,0.1);
        }
            .rees46-recommend .recommended-items .recommended-item:hover {
                box-shadow: 0 3px 2px rgba(0,0,0,0.2);
            }
            .rees46-recommend .recommended-items .recommended-item .recommended-item-photo {
                height: 160px;
                width: 100%;
                display: table;
                margin-bottom: 10px;
            }
                .rees46-recommend .recommended-items .recommended-item .recommended-item-photo > a {
                    display: table-cell;
                    vertical-align: middle;
                    height: 160px;
                }
                    .rees46-recommend .recommended-items .recommended-item .recommended-item-photo img {
                        max-width: 100%;
                        max-height: 160px;
                    }
            .rees46-recommend .recommended-items .recommended-item .recommended-item-title {
                overflow: hidden;
                margin-bottom: 20px;
                text-align: left;
                padding-bottom: 20px;
                border-bottom: 1px solid #E3E3DC;
            }
                .rees46-recommend .recommended-items .recommended-item .recommended-item-title > a {
                    display: block;
                    height: 30px;
                    max-height: 30px;
                    overflow: hidden;
                    text-decoration: none;
                    font-weight: bold;
                    color: #565555;
                }
            .rees46-recommend .recommended-items .recommended-item .recommended-item-price {
                float: left;
                width: 84px;
                text-align: left;
                font-weight: bold;
                padding: 3px 0;
                color: #3e3f3f;
            }
            .rees46-recommend .recommended-items .recommended-item .recommended-item-action {
                float: right;
            }
                .rees46-recommend .recommended-items .recommended-item .recommended-item-action > a {
                    background-color: #19538d;
                    background-image: -webkit-gradient(linear, left top, left bottom, from(rgb(25, 83, 141)), to(rgb(19, 67, 113)));
                    background-image: -webkit-linear-gradient(top, rgb(25, 83, 141), rgb(19, 67, 113));
                    background-image: -moz-linear-gradient(top, rgb(25, 83, 141), rgb(19, 67, 113));
                    background-image: -o-linear-gradient(top, rgb(25, 83, 141), rgb(19, 67, 113));
                    background-image: -ms-linear-gradient(top, rgb(25, 83, 141), rgb(19, 67, 113));
                    background-image: linear-gradient(top, rgb(25, 83, 141), rgb(19, 67, 113));
                    filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorStr='#19538d', EndColorStr='#134371');
                    color: #fff;
                    padding: 3px 10px;
                    display: inline-block;
                    text-decoration: none;
                    border: 1px solid #31659A;
                    box-shadow: 0 1px 1px rgba(0,0,0,0.5);
                }
                    .rees46-recommend .recommended-items .recommended-item .recommended-item-action > a:hover {
                        background-color: #1d5e9e;
                        background-image: -webkit-gradient(linear, left top, left bottom, from(rgb(29, 94, 158)), to(rgb(19, 67, 113)));
                        background-image: -webkit-linear-gradient(top, rgb(29, 94, 158), rgb(19, 67, 113));
                        background-image: -moz-linear-gradient(top, rgb(29, 94, 158), rgb(19, 67, 113));
                        background-image: -o-linear-gradient(top, rgb(29, 94, 158), rgb(19, 67, 113));
                        background-image: -ms-linear-gradient(top, rgb(29, 94, 158), rgb(19, 67, 113));
                        background-image: linear-gradient(top, rgb(29, 94, 158), rgb(19, 67, 113));
                        filter: progid:DXImageTransform.Microsoft.gradient(GradientType=0,StartColorStr='#1d5e9e', EndColorStr='#134371');
                    }mend {
    font-family: Arial, sans-serif;
    margin-bottom: 20px;
    display: table;
}
    .rees46-recommend .recommender-block-title {
        font-size: 22px;
        font-weight: bold;
            margin-bottom: 12px;
    }
    .rees46-recommend .recommended-items {
        overflow: hidden;
        height: 360px;
    }
        .rees46-recommend .recommended-items .recommended-item {
            display: block;
            float: left;
            margin: 0 10px 10px 0;
            width: 200px;
            padding: 10px;
            border: 1px solid #777;
            height: 330px;
            text-align: center;
            box-shadow: 0 2px 2px rgba(100,100,100,0.5);
        }
            .rees46-recommend .recommended-items .recommended-item:hover {
                box-shadow: 0 2px 2px rgba(100,100,100,0.8);
            }
            .rees46-recommend .recommended-items .recommended-item .recommended-item-photo {
                height: 200px;
                width: 100%;
                display: table;
            }
                .rees46-recommend .recommended-items .recommended-item .recommended-item-photo {}
                    .rees46-recommend .recommended-items .recommended-item .recommended-item-photo > a {
                        display: table-cell;
                        vertical-align: middle;
                        height: 200px;
                    }
                    .rees46-recommend .recommended-items .recommended-item .recommended-item-photo img {
                        max-width: 100%;
                        max-height: 200px;
                    }
            .rees46-recommend .recommended-items .recommended-item .recommended-item-title {
                max-height: 2.2em;
                height: 2.2em;
                overflow: hidden;
                margin-bottom: 10px;
                text-shadow: 0 1px 1px rgba(100,100,100,0.5);
            }
                .rees46-recommend .recommended-items .recommended-item .recommended-item-title > a {
                    text-decoration: none;
                    font-weight: bold;
                    color: #333;
                }
            .rees46-recommend .recommended-items .recommended-item .recommended-item-price {
                margin-bottom: 20px;
                color: green;
            }
            .rees46-recommend .recommended-items .recommended-item .recommended-item-action {}
                .rees46-recommend .recommended-items .recommended-item .recommended-item-action > a {
                    display: inline-block;
                    background: #6BC34B;
                    border-radius: 20px;
                    padding: 10px 20px;
                    font-size: 85%;
                    color: #000;
                    text-decoration: none;
                    box-shadow: 0 1px 2px rgba(100,100,100,0.9), 0 1px 1px rgba(255,255,255,0.8) inset;
                }
                    .rees46-recommend .recommended-items .recommended-item .recommended-item-action > a:hover {
                        background: #86CC66;
                    }

CSS
		);
	}

	public static function getShopID()
	{
		return \COption::GetOptionString(\mk_rees46::MODULE_ID, 'shop_id');
	}

	public static function getShopSecret()
	{
		return \COption::GetOptionString(\mk_rees46::MODULE_ID, 'shop_secret');
	}

	public static function getImageWidth()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'image_width', \mk_rees46::IMAGE_WIDTH_DEFAULT);
	}

	public static function getImageHeight()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'image_height', \mk_rees46::IMAGE_HEIGHT_DEFAULT);
	}

	public static function getRecommendCount()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'recommend_count', \mk_rees46::RECOMMEND_COUNT_DEFAULT);
	}

	public static function getRecommendNonAvailable()
	{
		return \COption::GetOptionInt(\mk_rees46::MODULE_ID, 'recommend_nonavailable', false) ? true : false;
	}
}
