-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1:3306
-- 產生時間： 2020-01-10 07:57:51
-- 伺服器版本： 5.7.26
-- PHP 版本： 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `linebot`
--

-- --------------------------------------------------------

--
-- 資料表結構 `get_event_list`
--

DROP TABLE IF EXISTS `get_event_list`;
CREATE TABLE IF NOT EXISTS `get_event_list` (
  `gel_id` int(11) NOT NULL AUTO_INCREMENT,
  `replyToken` text COLLATE utf8_unicode_ci,
  `userId` text COLLATE utf8_unicode_ci,
  `id` text COLLATE utf8_unicode_ci,
  `data` text COLLATE utf8_unicode_ci,
  `timestamp` text COLLATE utf8_unicode_ci,
  `create_time` int(30) DEFAULT '0',
  PRIMARY KEY (`gel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `response_list`
--

DROP TABLE IF EXISTS `response_list`;
CREATE TABLE IF NOT EXISTS `response_list` (
  `rl_id` int(200) NOT NULL AUTO_INCREMENT,
  `action` text COLLATE utf8_unicode_ci COMMENT '觸發action',
  `response` text COLLATE utf8_unicode_ci COMMENT '回覆',
  `create_time` int(30) DEFAULT '0',
  PRIMARY KEY (`rl_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- 傾印資料表的資料 `response_list`
--

INSERT INTO `response_list` (`rl_id`, `action`, `response`, `create_time`) VALUES
(1, '測試', '你要測試什麼?', 0),
(2, '我', '你是誰?', 0),
(3, '說甚麼', '我怎麼知道你要說甚麼好?', 0),
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
