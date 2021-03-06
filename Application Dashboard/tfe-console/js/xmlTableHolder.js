/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Code for AJAX in XML Table Holder
 */

/* Code for refresh XML Table using AJAX */

$(document).ready(function()
{
    refreshXML();
});

function refreshXML()
{
    $('#tableHolderXML').load('getXMLfile.php', function()
    {
        setTimeout(refreshXML, 2000);
    });
}
