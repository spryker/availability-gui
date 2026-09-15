/**
 * Copyright (c) 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

var tableAccess = require('ZedGuiModules/libs/table/table-access');

$(document).ready(function () {
    const bundledProducts = document.querySelector('#bundled-products');
    const bundledProductsTable = document.querySelector('#table-bundled-products');
    const availabilityTable = $('#availability-table');

    if (!bundledProducts || !bundledProductsTable || !availabilityTable.length) {
        return;
    }

    availabilityTable.on('click', '.btn-view', function (event) {
        event.preventDefault();

        const url = $(this).prop('href');

        bundledProducts.style.display = '';

        tableAccess.requestTable(bundledProductsTable, function (handle) {
            handle.reload(url).then(function () {
                handle.refreshLayout();
            });
        });
    });
});
