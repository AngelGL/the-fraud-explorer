﻿/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-06-30 15:12:41 -0500 (Wed, 30 Jun 2016)
 * Revision: v0.9.6-beta
 *
 * Description: Main Application
 */

using System;
using System.Windows;
using TFE_core.Database;
using TFE_core.Config;

namespace TFE_core
{ 
    public partial class App : Application
    {
        /// <summary>
        /// Embed DLL's attached to the executable
        /// </summary>
 
        #region Embed DLL's 

        public App() 
        {
            DLLEmbed.Load("TFE_core.Library.SQLite.Community.CsharpSqlite.dll", "Community.CsharpSqlite.dll");
            DLLEmbed.Load("TFE_core.Library.SQLite.Community.CsharpSqlite.SQLiteClient.dll", "Community.CsharpSqlite.SQLiteClient.dll");
            DLLEmbed.Load("TFE_core.Library.Log4Net.log4net.dll","log4net.dll");
            DLLEmbed.Load("TFE_core.Library.SharpPcap.PacketDotNet.dll", "PacketDotNet.dll");
            DLLEmbed.Load("TFE_core.Library.SharpPcap.SharpPcap.dll", "SharpPcap.dll");
            DLLEmbed.Load("TFE_core.Library.NDde.NDde.dll", "NDde.dll");

            AppDomain.CurrentDomain.AssemblyResolve += new ResolveEventHandler(CurrentDomain_AssemblyResolve);           
        }
      
        static System.Reflection.Assembly CurrentDomain_AssemblyResolve(object sender, ResolveEventArgs args)
        {
            return DLLEmbed.Get(args.Name);
        }

        #endregion

        /// <summary>
        /// Application starting method
        /// </summary>

        #region Application start

        private void Application_Startup(object sender, StartupEventArgs e)
        {
            // Prevent multiple executions

            Common.preventDuplicate();

            try
            {
                // Database initialization

                SQLStorage.DBInitializationChecks();

                // Registry preparation

                Common.registryChecks();

                // Start modules

                modulesControl mod = new modulesControl();
                mod.startModules();
                
            }
            catch { }
        }

        #endregion

        /// <summary>
        /// Application exiting
        /// </summary>

        #region Application exit

        private void Application_Exit(object sender, ExitEventArgs e) { }

        #endregion      
    }
}
