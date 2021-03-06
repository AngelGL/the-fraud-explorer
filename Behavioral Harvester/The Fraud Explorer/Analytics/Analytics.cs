﻿/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Analytics
 */

using System;
using log4net.Appender;
using log4net.Layout;
using TFE_core.Config;
using TFE_core.Database;

namespace TFE_core.Analytics
{
    class Analytics { }

    public class TextAnalyticsLogger
    {
        /// <summary>
        /// Setup TextAnalyticsLogger
        /// </summary>

        #region TextAnalyticsLogger

        private static System.Net.IPAddress analyticsIPAddress = System.Net.IPAddress.Parse(Settings.AnalyticsServerIP);

        public static void Setup_textAnalytics()
        {
            log4net.Repository.ILoggerRepository textAnalytics_Repo = log4net.LogManager.CreateRepository("textAnalytics_Repo");

            PatternLayout patternLayout_TextAnalytics = new PatternLayout();
            patternLayout_TextAnalytics.ConversionPattern = "%date %property{IPAddress} %property{log4net:UserName} %property{AgentID} %message - a: %property{TextWindow} b: %property{Word} %newline";
            patternLayout_TextAnalytics.ActivateOptions();

            UdpAppender UdpAppenderTA = new UdpAppender();
            UdpAppenderTA.RemoteAddress = analyticsIPAddress;
            UdpAppenderTA.RemotePort = Convert.ToInt32(SQLStorage.retrievePar(Settings.TPORTFLAG));
            UdpAppenderTA.Threshold = log4net.Core.Level.All;
            UdpAppenderTA.Layout = patternLayout_TextAnalytics;
            UdpAppenderTA.ActivateOptions();

            log4net.Config.BasicConfigurator.Configure(textAnalytics_Repo, UdpAppenderTA);
        }

        #endregion
    }
}
