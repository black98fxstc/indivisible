mysql --user root --password="$DB_PASSWORD" <<'EOF'
CREATE DATABASE legiscan_api DEFAULT CHARACTER SET utf8;
GRANT USAGE ON *.* TO 'legiscan_api'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT CREATE, ALTER, SELECT, INSERT, UPDATE, DELETE ON `legiscan_api`.* TO 'legiscan_api'@'localhost';
FLUSH PRIVILEGES;
EOF

mysql --user root --password="$DB_PASSWORD" -D legiscan_api <<'EOF'
--
-- Database: legiscan_api
--

-- --------------------------------------------------------

--
-- Table structure for table ls_bill
--

CREATE TABLE ls_bill (
  bill_id mediumint(8) UNSIGNED NOT NULL,
  state_id tinyint(3) UNSIGNED NOT NULL,
  session_id smallint(5) UNSIGNED NOT NULL,
  body_id tinyint(3) UNSIGNED NOT NULL,
  current_body_id tinyint(3) UNSIGNED NOT NULL,
  bill_type_id tinyint(3) UNSIGNED NOT NULL,
  bill_number varchar(10) NOT NULL,
  status_id tinyint(3) UNSIGNED NOT NULL,
  status_date date DEFAULT NULL,
  title text NOT NULL,
  description text NOT NULL,
  pending_committee_id smallint(5) UNSIGNED NOT NULL,
  legiscan_url varchar(255) NOT NULL,
  state_url varchar(255) NOT NULL,
  change_hash char(32) NOT NULL,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_amendment
--

CREATE TABLE ls_bill_amendment (
  amendment_id mediumint(8) UNSIGNED NOT NULL,
  bill_id mediumint(8) UNSIGNED NOT NULL,
  local_copy tinyint(3) UNSIGNED NOT NULL,
  adopted tinyint(3) UNSIGNED NOT NULL,
  amendment_body_id tinyint(3) UNSIGNED NOT NULL,
  amendment_mime_id tinyint(3) UNSIGNED NOT NULL,
  amendment_date date DEFAULT NULL,
  amendment_title varchar(255) NOT NULL,
  amendment_desc text NOT NULL,
  legiscan_url varchar(255) NOT NULL,
  state_url varchar(255) NOT NULL,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_calendar
--

CREATE TABLE ls_bill_calendar (
  bill_id mediumint(8) UNSIGNED NOT NULL,
  event_hash char(8) NOT NULL,
  event_type_id tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  event_date date DEFAULT NULL,
  event_time time DEFAULT NULL,
  event_location varchar(64) NOT NULL,
  event_desc varchar(128) NOT NULL,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_history
--

CREATE TABLE ls_bill_history (
  bill_id mediumint(8) UNSIGNED NOT NULL,
  history_step smallint(5) UNSIGNED NOT NULL,
  history_major tinyint(3) UNSIGNED NOT NULL,
  history_body_id tinyint(3) UNSIGNED NOT NULL,
  history_date date DEFAULT NULL,
  history_action text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_progress
--

CREATE TABLE ls_bill_progress (
  bill_id mediumint(8) UNSIGNED NOT NULL,
  progress_step tinyint(3) UNSIGNED NOT NULL,
  progress_date date DEFAULT NULL,
  progress_event_id tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_reason
--

CREATE TABLE ls_bill_reason (
  bill_id mediumint(8) UNSIGNED NOT NULL,
  reason_id tinyint(3) UNSIGNED NOT NULL,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_sast
--

CREATE TABLE ls_bill_sast (
  bill_id mediumint(8) UNSIGNED NOT NULL,
  sast_bill_id mediumint(8) UNSIGNED NOT NULL,
  sast_type_id tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_sponsor
--

CREATE TABLE ls_bill_sponsor (
  bill_id mediumint(8) UNSIGNED NOT NULL,
  sponsor_order smallint(5) UNSIGNED NOT NULL,
  people_id smallint(5) UNSIGNED NOT NULL,
  sponsor_type_id tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_subject
--

CREATE TABLE ls_bill_subject (
  bill_id mediumint(8) UNSIGNED NOT NULL,
  subject_id mediumint(8) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_supplement
--

CREATE TABLE ls_bill_supplement (
  supplement_id mediumint(8) UNSIGNED NOT NULL,
  bill_id mediumint(8) UNSIGNED NOT NULL,
  local_copy tinyint(3) UNSIGNED NOT NULL,
  supplement_type_id tinyint(3) UNSIGNED NOT NULL,
  supplement_mime_id tinyint(3) UNSIGNED NOT NULL,
  supplement_date date DEFAULT NULL,
  supplement_title varchar(255) NOT NULL,
  supplement_desc text NOT NULL,
  legiscan_url varchar(255) NOT NULL,
  state_url varchar(255) NOT NULL,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_text
--

CREATE TABLE ls_bill_text (
  text_id mediumint(8) UNSIGNED NOT NULL,
  bill_id mediumint(8) UNSIGNED NOT NULL,
  local_copy tinyint(3) UNSIGNED NOT NULL,
  bill_text_type_id tinyint(3) UNSIGNED NOT NULL,
  bill_text_mime_id tinyint(3) UNSIGNED NOT NULL,
  bill_text_date date DEFAULT NULL,
  legiscan_url varchar(255) NOT NULL,
  state_url varchar(255) NOT NULL,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_vote
--

CREATE TABLE ls_bill_vote (
  roll_call_id mediumint(8) UNSIGNED NOT NULL,
  bill_id mediumint(8) UNSIGNED NOT NULL,
  roll_call_body_id tinyint(3) UNSIGNED NOT NULL,
  roll_call_date date DEFAULT NULL,
  roll_call_desc varchar(255) NOT NULL,
  yea smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  nay smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  nv smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  absent smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  total smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  passed tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  legiscan_url varchar(255) NOT NULL,
  state_url varchar(255) NOT NULL,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_bill_vote_detail
--

CREATE TABLE ls_bill_vote_detail (
  roll_call_id mediumint(8) UNSIGNED NOT NULL,
  people_id smallint(5) UNSIGNED NOT NULL,
  vote_id tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_body
--

CREATE TABLE ls_body (
  body_id tinyint(3) UNSIGNED NOT NULL,
  state_id tinyint(3) UNSIGNED NOT NULL,
  role_id tinyint(3) UNSIGNED NOT NULL,
  body_abbr char(1) NOT NULL,
  body_short varchar(16) DEFAULT NULL,
  body_name varchar(128) NOT NULL,
  body_role_abbr varchar(3) DEFAULT NULL,
  body_role_name varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_body
--

INSERT INTO ls_body (body_id, state_id, role_id, body_abbr, body_short, body_name, body_role_abbr, body_role_name) VALUES
(1, 48, 1, 'H', 'House', 'House of Delegates', 'Del', 'Delegate'),
(2, 48, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(3, 38, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(4, 38, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(5, 35, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(6, 35, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(7, 46, 1, 'H', 'House', 'House of Delegates', 'Del', 'Delegate'),
(8, 46, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(11, 1, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(12, 1, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(13, 2, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(14, 2, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(15, 3, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(16, 3, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(17, 4, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(18, 4, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(19, 5, 1, 'A', 'Assembly', 'State Assembly', 'Asm', 'Assemblymember'),
(20, 5, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(21, 6, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(22, 6, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(23, 7, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(24, 7, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(25, 8, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(26, 8, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(27, 9, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(28, 9, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(29, 10, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(30, 10, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(31, 11, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(32, 11, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(33, 12, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(34, 12, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(35, 13, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(36, 13, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(37, 14, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(38, 14, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(39, 15, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(40, 15, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(41, 16, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(42, 16, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(43, 17, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(44, 17, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(45, 18, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(46, 18, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(47, 19, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(48, 19, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(49, 20, 1, 'H', 'House', 'House of Delegates', 'Del', 'Delegate'),
(50, 20, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(51, 21, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(52, 21, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(53, 22, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(54, 22, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(55, 23, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(56, 23, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(57, 24, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(58, 24, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(59, 25, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(60, 25, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(61, 26, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(62, 26, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(64, 27, 2, 'L', 'Legislature', 'Legislature', 'Sen', 'Senator'),
(65, 28, 1, 'A', 'Assembly', 'Assembly', 'Rep', 'Representative'),
(66, 28, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(67, 29, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(68, 29, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(69, 30, 1, 'A', 'Assembly', 'General Assembly', 'Rep', 'Representative'),
(70, 30, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(71, 31, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(72, 31, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(73, 32, 1, 'A', 'Assembly', 'Assembly', 'Asm', 'Assemblymember'),
(74, 32, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(75, 33, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(76, 33, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(77, 34, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(78, 34, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(79, 36, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(80, 36, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(81, 37, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(82, 37, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(83, 39, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(84, 39, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(85, 40, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(86, 40, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(87, 41, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(88, 41, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(89, 42, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(90, 42, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(91, 43, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(92, 43, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(93, 44, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(94, 44, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(95, 45, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(96, 45, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(97, 47, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(98, 47, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(99, 49, 1, 'A', 'House', 'State Assembly', 'Rep', 'Representative'),
(100, 49, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(101, 50, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(102, 50, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(103, 21, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(106, 49, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(107, 39, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(108, 7, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(109, 16, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(110, 34, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(111, 4, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(112, 50, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(113, 8, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(114, 52, 1, 'H', 'House', 'House of Representatives', 'Rep', 'Representative'),
(115, 52, 2, 'S', 'Senate', 'Senate', 'Sen', 'Senator'),
(116, 51, 2, 'C', 'Council', 'City Council', 'Cnc', 'Councilmember'),
(117, 36, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(118, 37, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(119, 19, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(120, 26, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(121, 42, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(122, 25, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(123, 52, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(124, 6, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(125, 15, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(126, 20, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint'),
(127, 41, 3, 'J', 'Joint', 'Joint Conference', 'Jnt', 'Joint');

-- --------------------------------------------------------

--
-- Table structure for table ls_committee
--

CREATE TABLE ls_committee (
  committee_id smallint(5) UNSIGNED NOT NULL,
  committee_body_id tinyint(3) UNSIGNED NOT NULL,
  committee_name varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_event_type
--

CREATE TABLE ls_event_type (
  event_type_id tinyint(4) NOT NULL,
  event_type_desc varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_event_type
--

INSERT INTO ls_event_type (event_type_id, event_type_desc) VALUES
(1, 'Hearing'),
(2, 'Executive Session'),
(3, 'Markup Session');

-- --------------------------------------------------------

--
-- Table structure for table ls_ignore
--

CREATE TABLE ls_ignore (
  bill_id mediumint(8) UNSIGNED NOT NULL,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_mime_type
--

CREATE TABLE ls_mime_type (
  mime_id tinyint(3) UNSIGNED NOT NULL,
  mime_type varchar(80) NOT NULL,
  is_binary tinyint(3) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_mime_type
--

INSERT INTO ls_mime_type (mime_id, mime_type, is_binary) VALUES
(1, 'text/html', 0),
(2, 'application/pdf', 1),
(3, 'application/wpd', 1),
(4, 'application/doc', 1),
(5, 'application/rtf', 1),
(6, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 1);

-- --------------------------------------------------------

--
-- Table structure for table ls_monitor
--

CREATE TABLE ls_monitor (
  bill_id mediumint(8) UNSIGNED NOT NULL,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_party
--

CREATE TABLE ls_party (
  party_id tinyint(3) UNSIGNED NOT NULL,
  party_abbr char(1) NOT NULL,
  party_short char(3) NOT NULL,
  party_name varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_party
--

INSERT INTO ls_party (party_id, party_abbr, party_short, party_name) VALUES
(1, 'D', 'Dem', 'Democrat'),
(2, 'R', 'Rep', 'Republican'),
(3, 'I', 'Ind', 'Independent'),
(4, 'G', 'Grn', 'Green Party'),
(5, 'L', 'Lib', 'Libertarian'),
(6, 'N', 'NP', 'Nonpartisan');

-- --------------------------------------------------------

--
-- Table structure for table ls_people
--

CREATE TABLE ls_people (
  people_id smallint(5) UNSIGNED NOT NULL,
  state_id tinyint(3) UNSIGNED NOT NULL,
  role_id tinyint(3) UNSIGNED NOT NULL,
  party_id tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  name varchar(128) NOT NULL,
  first_name varchar(32) NOT NULL,
  middle_name varchar(32) NOT NULL,
  last_name varchar(32) NOT NULL,
  suffix varchar(32) NOT NULL,
  nickname varchar(32) NOT NULL,
  district varchar(9) DEFAULT '',
  committee_sponsor_id smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  ballotpedia varchar(64) DEFAULT NULL,
  followthemoney_eid bigint(20) UNSIGNED NOT NULL DEFAULT '0',
  votesmart_id mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
  opensecrets_id char(9) DEFAULT NULL,
  person_hash char(8) NOT NULL,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_progress
--

CREATE TABLE ls_progress (
  progress_event_id tinyint(3) UNSIGNED NOT NULL,
  progress_desc varchar(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_progress
--

INSERT INTO ls_progress (progress_event_id, progress_desc) VALUES
(1, 'Introduced'),
(2, 'Engrossed'),
(3, 'Enrolled'),
(4, 'Passed'),
(5, 'Vetoed'),
(6, 'Failed'),
(7, 'Override'),
(8, 'Chaptered'),
(9, 'Refer'),
(10, 'Report Pass'),
(11, 'Report DNP'),
(12, 'Draft');

-- --------------------------------------------------------

--
-- Table structure for table ls_reason
--

CREATE TABLE ls_reason (
  reason_id tinyint(3) UNSIGNED NOT NULL,
  reason_desc varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_reason
--

INSERT INTO ls_reason (reason_id, reason_desc) VALUES
(1, 'NewBill'),
(2, 'StatusChange'),
(3, 'Chamber'),
(4, 'Complete'),
(5, 'Title'),
(6, 'Description'),
(7, 'CommRefer'),
(8, 'CommReport'),
(9, 'SponsorAdd'),
(10, 'SponsorRemove'),
(11, 'SponsorChange'),
(12, 'HistoryAdd'),
(13, 'HistoryRemove'),
(14, 'HistoryRevised'),
(15, 'HistoryMajor'),
(16, 'HistoryMinor'),
(17, 'SubjectAdd'),
(18, 'SubjectRemove'),
(19, 'SAST'),
(20, 'Text'),
(21, 'Amendment'),
(22, 'Supplement'),
(23, 'Vote'),
(24, 'Calendar'),
(25, 'Progress'),
(26, 'VoteUpdate'),
(27, 'TextUpdate'),
(99, 'ICBM');

-- --------------------------------------------------------

--
-- Table structure for table ls_role
--

CREATE TABLE ls_role (
  role_id tinyint(3) UNSIGNED NOT NULL,
  role_name varchar(64) NOT NULL,
  role_abbr char(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_role
--

INSERT INTO ls_role (role_id, role_name, role_abbr) VALUES
(1, 'Representative', 'Rep'),
(2, 'Senator', 'Sen'),
(3, 'Joint Conference', 'Jnt');

-- --------------------------------------------------------

--
-- Table structure for table ls_sast_type
--

CREATE TABLE ls_sast_type (
  sast_id tinyint(3) UNSIGNED NOT NULL,
  sast_description varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_sast_type
--

INSERT INTO ls_sast_type (sast_id, sast_description) VALUES
(1, 'Same As'),
(2, 'Similar To'),
(3, 'Replaced by'),
(4, 'Replaces'),
(5, 'Crossfiled'),
(6, 'Enabling for'),
(7, 'Enabled by'),
(8, 'Related'),
(9, 'Carry Over');

-- --------------------------------------------------------

--
-- Table structure for table ls_session
--

CREATE TABLE ls_session (
  session_id smallint(5) UNSIGNED NOT NULL,
  state_id tinyint(3) UNSIGNED NOT NULL,
  year_start smallint(5) UNSIGNED NOT NULL,
  year_end smallint(5) UNSIGNED NOT NULL,
  special tinyint(3) UNSIGNED NOT NULL,
  session_name varchar(128) NOT NULL,
  session_title varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_signal
--

CREATE TABLE ls_signal (
  object_type varchar(10) NOT NULL,
  object_id mediumint(8) UNSIGNED NOT NULL,
  processed tinyint(3) UNSIGNED NOT NULL,
  updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_sponsor_type
--

CREATE TABLE ls_sponsor_type (
  sponsor_type_id tinyint(3) UNSIGNED NOT NULL,
  sponsor_type_desc varchar(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_sponsor_type
--

INSERT INTO ls_sponsor_type (sponsor_type_id, sponsor_type_desc) VALUES
(0, 'Sponsor'),
(1, 'Primary Sponsor'),
(2, 'Co-Sponsor'),
(3, 'Joint Sponsor');

-- --------------------------------------------------------

--
-- Table structure for table ls_state
--

CREATE TABLE ls_state (
  state_id tinyint(3) UNSIGNED NOT NULL,
  state_abbr char(2) NOT NULL,
  state_name varchar(64) NOT NULL,
  biennium tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  carry_over char(2) DEFAULT NULL,
  capitol varchar(16) NOT NULL,
  latitude decimal(9,6) DEFAULT NULL,
  longitude decimal(9,6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_state
--

INSERT INTO ls_state (state_id, state_abbr, state_name, biennium, carry_over, capitol, latitude, longitude) VALUES
(1, 'AL', 'Alabama', 0, 'NO', 'Montgomery', '32.377716', '-86.300489'),
(2, 'AK', 'Alaska', 1, 'OE', 'Juneau', '58.301105', '-134.412957'),
(3, 'AZ', 'Arizona', 0, 'NO', 'Phoenix', '33.448113', '-112.097037'),
(4, 'AR', 'Arkansas', 0, 'NO', 'Little Rock', '34.746361', '-92.289422'),
(5, 'CA', 'California', 1, 'OE', 'Sacramento', '38.576700', '-121.493766'),
(6, 'CO', 'Colorado', 0, 'NO', 'Denver', '39.739276', '-104.984848'),
(7, 'CT', 'Connecticut', 0, 'NO', 'Hartford', '41.762831', '-72.682383'),
(8, 'DE', 'Delaware', 1, 'OE', 'Dover', '39.157354', '-75.519570'),
(9, 'FL', 'Florida', 0, 'NO', 'Tallahassee', '30.438086', '-84.282196'),
(10, 'GA', 'Georgia', 1, 'OE', 'Atlanta', '33.749035', '-84.388195'),
(11, 'HI', 'Hawaii', 0, 'OE', 'Honolulu', '21.294786', '-157.858818'),
(12, 'ID', 'Idaho', 0, 'NO', 'Boise', '43.617850', '-116.199940'),
(13, 'IL', 'Illinois', 1, 'OE', 'Springfield', '39.798358', '-89.654972'),
(14, 'IN', 'Indiana', 0, 'NO', 'Indianapolis', '39.768590', '-86.162634'),
(15, 'IA', 'Iowa', 1, 'OE', 'Des Moines', '41.591183', '-93.603694'),
(16, 'KS', 'Kansas', 1, 'OE', 'Topeka', '39.048070', '-95.678080'),
(17, 'KY', 'Kentucky', 0, 'NO', 'Frankfort', '38.186658', '-84.875265'),
(18, 'LA', 'Louisiana', 0, 'NO', 'Baton Rouge', '30.456615', '-91.187356'),
(19, 'ME', 'Maine', 1, 'OE', 'Augusta', '44.307185', '-69.781390'),
(20, 'MD', 'Maryland', 0, 'NO', 'Annapolis', '38.978862', '-76.490685'),
(21, 'MA', 'Massachusetts', 1, 'OE', 'Boston', '42.358424', '-71.063701'),
(22, 'MI', 'Michigan', 1, 'OE', 'Lansing', '42.733470', '-84.555300'),
(23, 'MN', 'Minnesota', 1, 'OE', 'Saint Paul', '44.948232', '-93.105406'),
(24, 'MS', 'Mississippi', 0, 'NO', 'Jackson', '32.303799', '-90.182005'),
(25, 'MO', 'Missouri', 0, 'NO', 'Jefferson City', '38.579206', '-92.173019'),
(26, 'MT', 'Montana', 0, 'NO', 'Helena', '46.585774', '-112.018180'),
(27, 'NE', 'Nebraska', 1, 'OE', 'Lincoln', '40.807935', '-96.699655'),
(28, 'NV', 'Nevada', 0, 'NO', 'Carson City', '39.164009', '-119.766153'),
(29, 'NH', 'New Hampshire', 0, 'OE', 'Concord', '43.206854', '-71.537659'),
(30, 'NJ', 'New Jersey', 1, 'EO', 'Trenton', '40.220280', '-74.770140'),
(31, 'NM', 'New Mexico', 0, 'NO', 'Santa Fe', '35.682440', '-105.940074'),
(32, 'NY', 'New York', 1, 'OE', 'Albany', '40.771120', '-73.974190'),
(33, 'NC', 'North Carolina', 1, 'OE', 'Raleigh', '35.780498', '-78.639110'),
(34, 'ND', 'North Dakota', 1, 'NO', 'Bismarck', '46.820900', '-100.781955'),
(35, 'OH', 'Ohio', 1, 'OE', 'Columbus', '39.961392', '-82.999065'),
(36, 'OK', 'Oklahoma', 0, 'OE', 'Oklahoma City', '35.492320', '-97.503340'),
(37, 'OR', 'Oregon', 0, 'NO', 'Salem', '44.938361', '-123.030155'),
(38, 'PA', 'Pennsylvania', 1, 'OE', 'Harrisburg', '40.264330', '-76.883521'),
(39, 'RI', 'Rhode Island', 0, 'OE', 'Providence', '41.831097', '-71.414883'),
(40, 'SC', 'South Carolina', 1, 'OE', 'Columbia', '34.000386', '-81.033210'),
(41, 'SD', 'South Dakota', 0, 'NO', 'Pierre', '44.367630', '-100.346040'),
(42, 'TN', 'Tennessee', 1, 'OE', 'Nashville', '36.166011', '-86.784297'),
(43, 'TX', 'Texas', 1, 'NO', 'Austin', '30.274001', '-97.740631'),
(44, 'UT', 'Utah', 0, 'NO', 'Salt Lake City', '40.777200', '-111.888280'),
(45, 'VT', 'Vermont', 1, 'OE', 'Montpelier', '44.262141', '-72.580716'),
(46, 'VA', 'Virginia', 0, 'EO', 'Richmond', '37.538783', '-77.433449'),
(47, 'WA', 'Washington', 1, 'OE', 'Olympia', '47.035964', '-122.904799'),
(48, 'WV', 'West Virginia', 0, 'OE', 'Charleston', '38.336166', '-81.612186'),
(49, 'WI', 'Wisconsin', 1, 'OE', 'Madison', '43.074530', '-89.384120'),
(50, 'WY', 'Wyoming', 0, 'NO', 'Cheyenne', '41.140101', '-104.820112'),
(51, 'DC', 'Washington D.C.', 1, 'OE', 'Washington, DC', '38.894825', '-77.031338'),
(52, 'US', 'US Congress', 1, 'OE', 'Washington, DC', '38.889873', '-77.008823');

-- --------------------------------------------------------

--
-- Table structure for table ls_subject
--

CREATE TABLE ls_subject (
  subject_id mediumint(8) UNSIGNED NOT NULL,
  state_id tinyint(3) UNSIGNED NOT NULL,
  subject_name varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table ls_supplement_type
--

CREATE TABLE ls_supplement_type (
  supplement_type_id tinyint(3) UNSIGNED NOT NULL,
  supplement_type_desc varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_supplement_type
--

INSERT INTO ls_supplement_type (supplement_type_id, supplement_type_desc) VALUES
(1, 'Fiscal Note'),
(2, 'Analysis'),
(3, 'Fiscal Note/Analysis'),
(4, 'Vote Image'),
(5, 'Local Mandate'),
(6, 'Corrections Impact'),
(7, 'Misc'),
(8, 'Veto Letter');

-- --------------------------------------------------------

--
-- Table structure for table ls_text_type
--

CREATE TABLE ls_text_type (
  bill_text_type_id tinyint(3) UNSIGNED NOT NULL,
  bill_text_name varchar(64) NOT NULL,
  bill_text_sort tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  bill_text_supplement tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_text_type
--

INSERT INTO ls_text_type (bill_text_type_id, bill_text_name, bill_text_sort, bill_text_supplement) VALUES
(1, 'Introduced', 2, 0),
(2, 'Comm Sub', 4, 0),
(3, 'Amended', 3, 0),
(4, 'Engrossed', 7, 0),
(5, 'Enrolled', 8, 0),
(6, 'Chaptered', 9, 0),
(7, 'Fiscal Note', 0, 1),
(8, 'Analysis', 0, 1),
(9, 'Draft', 1, 0),
(10, 'Conference Sub', 5, 0),
(11, 'Prefiled', 0, 0),
(12, 'Veto Message', 0, 1),
(13, 'Veto Response', 0, 1),
(14, 'Substitute', 6, 0);

-- --------------------------------------------------------

--
-- Table structure for table ls_type
--

CREATE TABLE ls_type (
  bill_type_id tinyint(3) UNSIGNED NOT NULL,
  bill_type_name varchar(64) NOT NULL,
  bill_type_abbr varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_type
--

INSERT INTO ls_type (bill_type_id, bill_type_name, bill_type_abbr) VALUES
(0, 'Committee Bill', 'CB'),
(1, 'Bill', 'B'),
(2, 'Resolution', 'R'),
(3, 'Concurrent Resolution', 'CR'),
(4, 'Joint Resolution', 'JR'),
(5, 'Joint Resolution Constitutional Amendment', 'JRCA'),
(6, 'Executive Order', 'EO'),
(7, 'Constitutional Amendment', 'CA'),
(8, 'Memorial', 'M'),
(9, 'Claim', 'CL'),
(10, 'Commendation', 'C'),
(11, 'Committee Study Request', 'CSR'),
(12, 'Joint Memorial', 'JM'),
(13, 'Proclamation', 'P'),
(14, 'Study Request', 'SR'),
(15, 'Address', 'A'),
(16, 'Concurrent Memorial', 'CM'),
(17, 'Initiative', 'I'),
(18, 'Petition', 'PET'),
(19, 'Study Bill', 'SB'),
(20, 'Initiative Petition', 'IP'),
(21, 'Repeal Bill', 'RB'),
(22, 'Remonstration', 'RM'),
(23, 'Committee Bill', 'CB');

-- --------------------------------------------------------

--
-- Table structure for table ls_vote
--

CREATE TABLE ls_vote (
  vote_id tinyint(3) UNSIGNED NOT NULL,
  vote_desc varchar(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table ls_vote
--

INSERT INTO ls_vote (vote_id, vote_desc) VALUES
(1, 'Yea'),
(2, 'Nay'),
(3, 'Not Voting'),
(4, 'Absent');

--
-- Indexes for dumped tables
--

--
-- Indexes for table ls_bill
--
ALTER TABLE ls_bill
  ADD PRIMARY KEY (bill_id),
  ADD KEY state_id (state_id),
  ADD KEY session_id (session_id),
  ADD KEY bill_number (bill_number);

--
-- Indexes for table ls_bill_amendment
--
ALTER TABLE ls_bill_amendment
  ADD PRIMARY KEY (amendment_id),
  ADD KEY bill_id (bill_id);

--
-- Indexes for table ls_bill_calendar
--
ALTER TABLE ls_bill_calendar
  ADD PRIMARY KEY (bill_id,event_hash);

--
-- Indexes for table ls_bill_history
--
ALTER TABLE ls_bill_history
  ADD PRIMARY KEY (bill_id,history_step);

--
-- Indexes for table ls_bill_progress
--
ALTER TABLE ls_bill_progress
  ADD PRIMARY KEY (bill_id,progress_step);

--
-- Indexes for table ls_bill_reason
--
ALTER TABLE ls_bill_reason
  ADD KEY bill_id (bill_id);

--
-- Indexes for table ls_bill_sast
--
ALTER TABLE ls_bill_sast
  ADD KEY bill_id (bill_id),
  ADD KEY sast_id (sast_bill_id);

--
-- Indexes for table ls_bill_sponsor
--
ALTER TABLE ls_bill_sponsor
  ADD PRIMARY KEY (bill_id,sponsor_order);

--
-- Indexes for table ls_bill_subject
--
ALTER TABLE ls_bill_subject
  ADD KEY bill_id (bill_id),
  ADD KEY subject_id (subject_id);

--
-- Indexes for table ls_bill_supplement
--
ALTER TABLE ls_bill_supplement
  ADD PRIMARY KEY (supplement_id),
  ADD KEY bill_id (bill_id);

--
-- Indexes for table ls_bill_text
--
ALTER TABLE ls_bill_text
  ADD PRIMARY KEY (text_id),
  ADD KEY bill_id (bill_id);

--
-- Indexes for table ls_bill_vote
--
ALTER TABLE ls_bill_vote
  ADD PRIMARY KEY (roll_call_id),
  ADD KEY bill_id (bill_id);

--
-- Indexes for table ls_bill_vote_detail
--
ALTER TABLE ls_bill_vote_detail
  ADD KEY roll_call_id (roll_call_id),
  ADD KEY people_id (people_id);

--
-- Indexes for table ls_body
--
ALTER TABLE ls_body
  ADD PRIMARY KEY (body_id),
  ADD KEY state_id (state_id),
  ADD KEY role_id (role_id),
  ADD KEY body_abbr (body_abbr);

--
-- Indexes for table ls_committee
--
ALTER TABLE ls_committee
  ADD PRIMARY KEY (committee_id),
  ADD KEY body_id (committee_body_id);

--
-- Indexes for table ls_event_type
--
ALTER TABLE ls_event_type
  ADD PRIMARY KEY (event_type_id);

--
-- Indexes for table ls_ignore
--
ALTER TABLE ls_ignore
  ADD PRIMARY KEY (bill_id);

--
-- Indexes for table ls_mime_type
--
ALTER TABLE ls_mime_type
  ADD PRIMARY KEY (mime_id);

--
-- Indexes for table ls_monitor
--
ALTER TABLE ls_monitor
  ADD PRIMARY KEY (bill_id);

--
-- Indexes for table ls_party
--
ALTER TABLE ls_party
  ADD PRIMARY KEY (party_id);

--
-- Indexes for table ls_people
--
ALTER TABLE ls_people
  ADD PRIMARY KEY (people_id),
  ADD KEY state_id (state_id),
  ADD KEY role_id (role_id),
  ADD KEY party_id (party_id);

--
-- Indexes for table ls_progress
--
ALTER TABLE ls_progress
  ADD PRIMARY KEY (progress_event_id);

--
-- Indexes for table ls_reason
--
ALTER TABLE ls_reason
  ADD PRIMARY KEY (reason_id);

--
-- Indexes for table ls_role
--
ALTER TABLE ls_role
  ADD PRIMARY KEY (role_id);

--
-- Indexes for table ls_sast_type
--
ALTER TABLE ls_sast_type
  ADD PRIMARY KEY (sast_id);

--
-- Indexes for table ls_session
--
ALTER TABLE ls_session
  ADD PRIMARY KEY (session_id);

--
-- Indexes for table ls_signal
--
ALTER TABLE ls_signal
  ADD PRIMARY KEY (object_type,object_id);

--
-- Indexes for table ls_sponsor_type
--
ALTER TABLE ls_sponsor_type
  ADD PRIMARY KEY (sponsor_type_id);

--
-- Indexes for table ls_state
--
ALTER TABLE ls_state
  ADD PRIMARY KEY (state_id),
  ADD KEY state_abbr (state_abbr);

--
-- Indexes for table ls_subject
--
ALTER TABLE ls_subject
  ADD PRIMARY KEY (subject_id),
  ADD KEY state_id (state_id);

--
-- Indexes for table ls_supplement_type
--
ALTER TABLE ls_supplement_type
  ADD PRIMARY KEY (supplement_type_id);

--
-- Indexes for table ls_text_type
--
ALTER TABLE ls_text_type
  ADD PRIMARY KEY (bill_text_type_id);

--
-- Indexes for table ls_type
--
ALTER TABLE ls_type
  ADD PRIMARY KEY (bill_type_id);

--
-- Indexes for table ls_vote
--
ALTER TABLE ls_vote
  ADD PRIMARY KEY (vote_id);
EOF
