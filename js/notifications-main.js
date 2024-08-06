(()=>{(()=>{"use strict";var ir={1473:(C,w,m)=>{m.d(w,{Z:()=>D});var B=m(7537),g=m.n(B),v=m(3645),l=m.n(v),x=m(1667),N=m.n(x),I=new URL(m(983),m.b),y=new URL(m(1391),m.b),$=l()(g()),Z=N()(I),P=N()(y);$.push([C.id,`/*!
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
.toastify.dialogs {
  min-width: 200px;
  background: none;
  background-color: var(--color-main-background);
  color: var(--color-main-text);
  box-shadow: 0 0 6px 0 var(--color-box-shadow);
  padding: 0 12px;
  margin-top: 45px;
  position: fixed;
  z-index: 10100;
  border-radius: var(--border-radius);
  display: flex;
  align-items: center;
}
.toastify.dialogs .toast-undo-container {
  display: flex;
  align-items: center;
}
.toastify.dialogs .toast-undo-button,
.toastify.dialogs .toast-close {
  position: static;
  overflow: hidden;
  box-sizing: border-box;
  min-width: 44px;
  height: 100%;
  padding: 12px;
  white-space: nowrap;
  background-repeat: no-repeat;
  background-position: center;
  background-color: transparent;
  min-height: 0;
}
.toastify.dialogs .toast-undo-button.toast-close,
.toastify.dialogs .toast-close.toast-close {
  text-indent: 0;
  opacity: 0.4;
  border: none;
  min-height: 44px;
  margin-left: 10px;
  font-size: 0;
  /* dark theme overrides for Nextcloud 25 and later */
}
.toastify.dialogs .toast-undo-button.toast-close::before,
.toastify.dialogs .toast-close.toast-close::before {
  background-image: url(${Z});
  content: " ";
  filter: var(--background-invert-if-dark);
  display: inline-block;
  width: 16px;
  height: 16px;
}
.toastify.dialogs .toast-undo-button.toast-undo-button,
.toastify.dialogs .toast-close.toast-undo-button {
  margin: 3px;
  height: calc(100% - 2 * 3px);
  margin-left: 12px;
}
.toastify.dialogs .toast-undo-button:hover, .toastify.dialogs .toast-undo-button:focus, .toastify.dialogs .toast-undo-button:active,
.toastify.dialogs .toast-close:hover,
.toastify.dialogs .toast-close:focus,
.toastify.dialogs .toast-close:active {
  cursor: pointer;
  opacity: 1;
}
.toastify.dialogs.toastify-top {
  right: 10px;
}
.toastify.dialogs.toast-with-click {
  cursor: pointer;
}
.toastify.dialogs.toast-error {
  border-left: 3px solid var(--color-error);
}
.toastify.dialogs.toast-info {
  border-left: 3px solid var(--color-primary);
}
.toastify.dialogs.toast-warning {
  border-left: 3px solid var(--color-warning);
}
.toastify.dialogs.toast-success {
  border-left: 3px solid var(--color-success);
}
.toastify.dialogs.toast-undo {
  border-left: 3px solid var(--color-success);
}

/* dark theme overrides for Nextcloud 24 and earlier */
.theme--dark .toastify.dialogs .toast-close {
  /* close icon style */
}
.theme--dark .toastify.dialogs .toast-close.toast-close::before {
  background-image: url(${P});
}
.nc-generic-dialog .dialog__actions {
	justify-content: space-between;
	min-width: calc(100% - 12px);
}
/*!
 * SPDX-FileCopyrightText: 2023-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
/**
 * Icon styling of the file list row preview or fallback icon
 * (leading icon on the name row and header)
 */
._file-picker__file-icon_19mjt_9 {
  width: 32px;
  height: 32px;
  min-width: 32px;
  min-height: 32px;
  background-repeat: no-repeat;
  background-size: contain;
  display: flex;
  justify-content: center;
}/*!
 * SPDX-FileCopyrightText: 2023-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
tr.file-picker__row[data-v-15187afc] {
  height: var(--row-height, 50px);
}
tr.file-picker__row td[data-v-15187afc] {
  cursor: pointer;
  overflow: hidden;
  text-overflow: ellipsis;
  border-bottom: none;
}
tr.file-picker__row td.row-checkbox[data-v-15187afc] {
  padding: 0 2px;
}
tr.file-picker__row td[data-v-15187afc]:not(.row-checkbox) {
  padding-inline: 14px 0;
}
tr.file-picker__row td.row-size[data-v-15187afc] {
  text-align: end;
  padding-inline: 0 14px;
}
tr.file-picker__row td.row-name[data-v-15187afc] {
  padding-inline: 2px 0;
}
@keyframes gradient-15187afc {
0% {
    background-position: 0% 50%;
}
50% {
    background-position: 100% 50%;
}
100% {
    background-position: 0% 50%;
}
}
.loading-row .row-checkbox[data-v-15187afc] {
  text-align: center !important;
}
.loading-row span[data-v-15187afc] {
  display: inline-block;
  height: 24px;
  background: linear-gradient(to right, var(--color-background-darker), var(--color-text-maxcontrast), var(--color-background-darker));
  background-size: 600px 100%;
  border-radius: var(--border-radius);
  animation: gradient-15187afc 12s ease infinite;
}
.loading-row .row-wrapper[data-v-15187afc] {
  display: inline-flex;
  align-items: center;
}
.loading-row .row-checkbox span[data-v-15187afc] {
  width: 24px;
}
.loading-row .row-name span[data-v-15187afc]:last-of-type {
  margin-inline-start: 6px;
  width: 130px;
}
.loading-row .row-size span[data-v-15187afc] {
  width: 80px;
}
.loading-row .row-modified span[data-v-15187afc] {
  width: 90px;
}/*!
 * SPDX-FileCopyrightText: 2023-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
tr.file-picker__row[data-v-cb12dccb] {
  height: var(--row-height, 50px);
}
tr.file-picker__row td[data-v-cb12dccb] {
  cursor: pointer;
  overflow: hidden;
  text-overflow: ellipsis;
  border-bottom: none;
}
tr.file-picker__row td.row-checkbox[data-v-cb12dccb] {
  padding: 0 2px;
}
tr.file-picker__row td[data-v-cb12dccb]:not(.row-checkbox) {
  padding-inline: 14px 0;
}
tr.file-picker__row td.row-size[data-v-cb12dccb] {
  text-align: end;
  padding-inline: 0 14px;
}
tr.file-picker__row td.row-name[data-v-cb12dccb] {
  padding-inline: 2px 0;
}
.file-picker__row--selected[data-v-cb12dccb] {
  background-color: var(--color-background-dark);
}
.file-picker__row[data-v-cb12dccb]:hover {
  background-color: var(--color-background-hover);
}
.file-picker__name-container[data-v-cb12dccb] {
  display: flex;
  justify-content: start;
  align-items: center;
  height: 100%;
}
.file-picker__file-name[data-v-cb12dccb] {
  padding-inline-start: 6px;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
}
.file-picker__file-extension[data-v-cb12dccb] {
  color: var(--color-text-maxcontrast);
  min-width: fit-content;
}.file-picker__header-preview[data-v-006fdbd0] {
  width: 22px;
  height: 32px;
  flex: 0 0 auto;
}
.file-picker__files[data-v-006fdbd0] {
  margin: 2px;
  margin-inline-start: 12px;
  overflow: scroll auto;
}
.file-picker__files table[data-v-006fdbd0] {
  width: 100%;
  max-height: 100%;
  table-layout: fixed;
}
.file-picker__files th[data-v-006fdbd0] {
  position: sticky;
  z-index: 1;
  top: 0;
  background-color: var(--color-main-background);
  padding: 2px;
}
.file-picker__files th .header-wrapper[data-v-006fdbd0] {
  display: flex;
}
.file-picker__files th.row-checkbox[data-v-006fdbd0] {
  width: 44px;
}
.file-picker__files th.row-name[data-v-006fdbd0] {
  width: 230px;
}
.file-picker__files th.row-size[data-v-006fdbd0] {
  width: 100px;
}
.file-picker__files th.row-modified[data-v-006fdbd0] {
  width: 120px;
}
.file-picker__files th[data-v-006fdbd0]:not(.row-size) .button-vue__wrapper {
  justify-content: start;
  flex-direction: row-reverse;
}
.file-picker__files th[data-v-006fdbd0]:not(.row-size) .button-vue {
  padding-inline: 16px 4px;
}
.file-picker__files th.row-size[data-v-006fdbd0] .button-vue__wrapper {
  justify-content: end;
}
.file-picker__files th[data-v-006fdbd0] .button-vue__wrapper {
  color: var(--color-text-maxcontrast);
}
.file-picker__files th[data-v-006fdbd0] .button-vue__wrapper .button-vue__text {
  font-weight: normal;
}.file-picker__breadcrumbs[data-v-b357227a] {
  flex-grow: 0 !important;
}.file-picker__side[data-v-b42054b8] {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 0.5rem;
  min-width: 200px;
  padding: 2px;
  margin-block-start: 7px;
  overflow: auto;
}
.file-picker__side[data-v-b42054b8] .button-vue__wrapper {
  justify-content: start;
}
.file-picker__filter-input[data-v-b42054b8] {
  margin-block: 7px;
  max-width: 260px;
}
@media (max-width: 736px) {
.file-picker__side[data-v-b42054b8] {
    flex-direction: row;
    min-width: unset;
}
}
@media (max-width: 512px) {
.file-picker__side[data-v-b42054b8] {
    flex-direction: row;
    min-width: unset;
}
.file-picker__filter-input[data-v-b42054b8] {
    max-width: unset;
}
}/* Ensure focus outline is visible */
.file-picker__navigation {
  padding-inline: 8px 2px;
}
.file-picker__navigation, .file-picker__navigation * {
  box-sizing: border-box;
}
.file-picker__navigation .v-select.select {
  min-width: 220px;
}
@media (min-width: 513px) and (max-width: 736px) {
.file-picker__navigation {
    gap: 11px;
}
}
@media (max-width: 512px) {
.file-picker__navigation {
    flex-direction: column-reverse !important;
}
}.file-picker__view[data-v-20b719ba] {
  height: 50px;
  display: flex;
  justify-content: start;
  align-items: center;
}
.file-picker__view h3[data-v-20b719ba] {
  font-weight: bold;
  height: fit-content;
  margin: 0;
}
.file-picker__main[data-v-20b719ba] {
  box-sizing: border-box;
  width: 100%;
  display: flex;
  flex-direction: column;
  min-height: 0;
  flex: 1;
  padding-inline: 2px;
}
.file-picker__main *[data-v-20b719ba] {
  box-sizing: border-box;
}
[data-v-20b719ba] .file-picker {
  height: min(80vh, 800px) !important;
}
@media (max-width: 512px) {
[data-v-20b719ba] .file-picker {
    height: calc(100% - 16px - var(--default-clickable-area)) !important;
}
}
[data-v-20b719ba] .file-picker__content {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}`,"",{version:3,sources:["webpack://./node_modules/@nextcloud/dialogs/dist/style.css"],names:[],mappings:"AAAA;;;EAGE;AACF;EACE,gBAAgB;EAChB,gBAAgB;EAChB,8CAA8C;EAC9C,6BAA6B;EAC7B,6CAA6C;EAC7C,eAAe;EACf,gBAAgB;EAChB,eAAe;EACf,cAAc;EACd,mCAAmC;EACnC,aAAa;EACb,mBAAmB;AACrB;AACA;EACE,aAAa;EACb,mBAAmB;AACrB;AACA;;EAEE,gBAAgB;EAChB,gBAAgB;EAChB,sBAAsB;EACtB,eAAe;EACf,YAAY;EACZ,aAAa;EACb,mBAAmB;EACnB,4BAA4B;EAC5B,2BAA2B;EAC3B,6BAA6B;EAC7B,aAAa;AACf;AACA;;EAEE,cAAc;EACd,YAAY;EACZ,YAAY;EACZ,gBAAgB;EAChB,iBAAiB;EACjB,YAAY;EACZ,oDAAoD;AACtD;AACA;;EAEE,yDAAsf;EACtf,YAAY;EACZ,wCAAwC;EACxC,qBAAqB;EACrB,WAAW;EACX,YAAY;AACd;AACA;;EAEE,WAAW;EACX,4BAA4B;EAC5B,iBAAiB;AACnB;AACA;;;;EAIE,eAAe;EACf,UAAU;AACZ;AACA;EACE,WAAW;AACb;AACA;EACE,eAAe;AACjB;AACA;EACE,yCAAyC;AAC3C;AACA;EACE,2CAA2C;AAC7C;AACA;EACE,2CAA2C;AAC7C;AACA;EACE,2CAA2C;AAC7C;AACA;EACE,2CAA2C;AAC7C;;AAEA,sDAAsD;AACtD;EACE,qBAAqB;AACvB;AACA;EACE,yDAAkgB;AACpgB;AACA;CACC,8BAA8B;CAC9B,4BAA4B;AAC7B;AACA;;;EAGE;AACF;;;EAGE;AACF;EACE,WAAW;EACX,YAAY;EACZ,eAAe;EACf,gBAAgB;EAChB,4BAA4B;EAC5B,wBAAwB;EACxB,aAAa;EACb,uBAAuB;AACzB,CAAC;;;EAGC;AACF;EACE,+BAA+B;AACjC;AACA;EACE,eAAe;EACf,gBAAgB;EAChB,uBAAuB;EACvB,mBAAmB;AACrB;AACA;EACE,cAAc;AAChB;AACA;EACE,sBAAsB;AACxB;AACA;EACE,eAAe;EACf,sBAAsB;AACxB;AACA;EACE,qBAAqB;AACvB;AACA;AACA;IACI,2BAA2B;AAC/B;AACA;IACI,6BAA6B;AACjC;AACA;IACI,2BAA2B;AAC/B;AACA;AACA;EACE,6BAA6B;AAC/B;AACA;EACE,qBAAqB;EACrB,YAAY;EACZ,oIAAoI;EACpI,2BAA2B;EAC3B,mCAAmC;EACnC,8CAA8C;AAChD;AACA;EACE,oBAAoB;EACpB,mBAAmB;AACrB;AACA;EACE,WAAW;AACb;AACA;EACE,wBAAwB;EACxB,YAAY;AACd;AACA;EACE,WAAW;AACb;AACA;EACE,WAAW;AACb,CAAC;;;EAGC;AACF;EACE,+BAA+B;AACjC;AACA;EACE,eAAe;EACf,gBAAgB;EAChB,uBAAuB;EACvB,mBAAmB;AACrB;AACA;EACE,cAAc;AAChB;AACA;EACE,sBAAsB;AACxB;AACA;EACE,eAAe;EACf,sBAAsB;AACxB;AACA;EACE,qBAAqB;AACvB;AACA;EACE,8CAA8C;AAChD;AACA;EACE,+CAA+C;AACjD;AACA;EACE,aAAa;EACb,sBAAsB;EACtB,mBAAmB;EACnB,YAAY;AACd;AACA;EACE,yBAAyB;EACzB,YAAY;EACZ,gBAAgB;EAChB,uBAAuB;AACzB;AACA;EACE,oCAAoC;EACpC,sBAAsB;AACxB,CAAC;EACC,WAAW;EACX,YAAY;EACZ,cAAc;AAChB;AACA;EACE,WAAW;EACX,yBAAyB;EACzB,qBAAqB;AACvB;AACA;EACE,WAAW;EACX,gBAAgB;EAChB,mBAAmB;AACrB;AACA;EACE,gBAAgB;EAChB,UAAU;EACV,MAAM;EACN,8CAA8C;EAC9C,YAAY;AACd;AACA;EACE,aAAa;AACf;AACA;EACE,WAAW;AACb;AACA;EACE,YAAY;AACd;AACA;EACE,YAAY;AACd;AACA;EACE,YAAY;AACd;AACA;EACE,sBAAsB;EACtB,2BAA2B;AAC7B;AACA;EACE,wBAAwB;AAC1B;AACA;EACE,oBAAoB;AACtB;AACA;EACE,oCAAoC;AACtC;AACA;EACE,mBAAmB;AACrB,CAAC;EACC,uBAAuB;AACzB,CAAC;EACC,aAAa;EACb,sBAAsB;EACtB,oBAAoB;EACpB,WAAW;EACX,gBAAgB;EAChB,YAAY;EACZ,uBAAuB;EACvB,cAAc;AAChB;AACA;EACE,sBAAsB;AACxB;AACA;EACE,iBAAiB;EACjB,gBAAgB;AAClB;AACA;AACA;IACI,mBAAmB;IACnB,gBAAgB;AACpB;AACA;AACA;AACA;IACI,mBAAmB;IACnB,gBAAgB;AACpB;AACA;IACI,gBAAgB;AACpB;AACA,CAAC,oCAAoC;AACrC;EACE,uBAAuB;AACzB;AACA;EACE,sBAAsB;AACxB;AACA;EACE,gBAAgB;AAClB;AACA;AACA;IACI,SAAS;AACb;AACA;AACA;AACA;IACI,yCAAyC;AAC7C;AACA,CAAC;EACC,YAAY;EACZ,aAAa;EACb,sBAAsB;EACtB,mBAAmB;AACrB;AACA;EACE,iBAAiB;EACjB,mBAAmB;EACnB,SAAS;AACX;AACA;EACE,sBAAsB;EACtB,WAAW;EACX,aAAa;EACb,sBAAsB;EACtB,aAAa;EACb,OAAO;EACP,mBAAmB;AACrB;AACA;EACE,sBAAsB;AACxB;AACA;EACE,mCAAmC;AACrC;AACA;AACA;IACI,oEAAoE;AACxE;AACA;AACA;EACE,aAAa;EACb,sBAAsB;EACtB,gBAAgB;AAClB",sourcesContent:[`/*!
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
.toastify.dialogs {
  min-width: 200px;
  background: none;
  background-color: var(--color-main-background);
  color: var(--color-main-text);
  box-shadow: 0 0 6px 0 var(--color-box-shadow);
  padding: 0 12px;
  margin-top: 45px;
  position: fixed;
  z-index: 10100;
  border-radius: var(--border-radius);
  display: flex;
  align-items: center;
}
.toastify.dialogs .toast-undo-container {
  display: flex;
  align-items: center;
}
.toastify.dialogs .toast-undo-button,
.toastify.dialogs .toast-close {
  position: static;
  overflow: hidden;
  box-sizing: border-box;
  min-width: 44px;
  height: 100%;
  padding: 12px;
  white-space: nowrap;
  background-repeat: no-repeat;
  background-position: center;
  background-color: transparent;
  min-height: 0;
}
.toastify.dialogs .toast-undo-button.toast-close,
.toastify.dialogs .toast-close.toast-close {
  text-indent: 0;
  opacity: 0.4;
  border: none;
  min-height: 44px;
  margin-left: 10px;
  font-size: 0;
  /* dark theme overrides for Nextcloud 25 and later */
}
.toastify.dialogs .toast-undo-button.toast-close::before,
.toastify.dialogs .toast-close.toast-close::before {
  background-image: url("data:image/svg+xml,%3csvg%20viewBox='0%200%2016%2016'%20height='16'%20width='16'%20xmlns='http://www.w3.org/2000/svg'%20xml:space='preserve'%20style='fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2'%3e%3cpath%20d='M6.4%2019%205%2017.6l5.6-5.6L5%206.4%206.4%205l5.6%205.6L17.6%205%2019%206.4%2013.4%2012l5.6%205.6-1.4%201.4-5.6-5.6L6.4%2019Z'%20style='fill-rule:nonzero'%20transform='matrix(.85714%200%200%20.85714%20-2.286%20-2.286)'/%3e%3c/svg%3e");
  content: " ";
  filter: var(--background-invert-if-dark);
  display: inline-block;
  width: 16px;
  height: 16px;
}
.toastify.dialogs .toast-undo-button.toast-undo-button,
.toastify.dialogs .toast-close.toast-undo-button {
  margin: 3px;
  height: calc(100% - 2 * 3px);
  margin-left: 12px;
}
.toastify.dialogs .toast-undo-button:hover, .toastify.dialogs .toast-undo-button:focus, .toastify.dialogs .toast-undo-button:active,
.toastify.dialogs .toast-close:hover,
.toastify.dialogs .toast-close:focus,
.toastify.dialogs .toast-close:active {
  cursor: pointer;
  opacity: 1;
}
.toastify.dialogs.toastify-top {
  right: 10px;
}
.toastify.dialogs.toast-with-click {
  cursor: pointer;
}
.toastify.dialogs.toast-error {
  border-left: 3px solid var(--color-error);
}
.toastify.dialogs.toast-info {
  border-left: 3px solid var(--color-primary);
}
.toastify.dialogs.toast-warning {
  border-left: 3px solid var(--color-warning);
}
.toastify.dialogs.toast-success {
  border-left: 3px solid var(--color-success);
}
.toastify.dialogs.toast-undo {
  border-left: 3px solid var(--color-success);
}

/* dark theme overrides for Nextcloud 24 and earlier */
.theme--dark .toastify.dialogs .toast-close {
  /* close icon style */
}
.theme--dark .toastify.dialogs .toast-close.toast-close::before {
  background-image: url("data:image/svg+xml,%3csvg%20viewBox='0%200%2016%2016'%20height='16'%20width='16'%20xmlns='http://www.w3.org/2000/svg'%20xml:space='preserve'%20style='fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2'%3e%3cpath%20d='M6.4%2019%205%2017.6l5.6-5.6L5%206.4%206.4%205l5.6%205.6L17.6%205%2019%206.4%2013.4%2012l5.6%205.6-1.4%201.4-5.6-5.6L6.4%2019Z'%20style='fill:%23fff;fill-rule:nonzero'%20transform='matrix(.85714%200%200%20.85714%20-2.286%20-2.286)'/%3e%3c/svg%3e");
}
.nc-generic-dialog .dialog__actions {
	justify-content: space-between;
	min-width: calc(100% - 12px);
}
/*!
 * SPDX-FileCopyrightText: 2023-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
/**
 * Icon styling of the file list row preview or fallback icon
 * (leading icon on the name row and header)
 */
._file-picker__file-icon_19mjt_9 {
  width: 32px;
  height: 32px;
  min-width: 32px;
  min-height: 32px;
  background-repeat: no-repeat;
  background-size: contain;
  display: flex;
  justify-content: center;
}/*!
 * SPDX-FileCopyrightText: 2023-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
tr.file-picker__row[data-v-15187afc] {
  height: var(--row-height, 50px);
}
tr.file-picker__row td[data-v-15187afc] {
  cursor: pointer;
  overflow: hidden;
  text-overflow: ellipsis;
  border-bottom: none;
}
tr.file-picker__row td.row-checkbox[data-v-15187afc] {
  padding: 0 2px;
}
tr.file-picker__row td[data-v-15187afc]:not(.row-checkbox) {
  padding-inline: 14px 0;
}
tr.file-picker__row td.row-size[data-v-15187afc] {
  text-align: end;
  padding-inline: 0 14px;
}
tr.file-picker__row td.row-name[data-v-15187afc] {
  padding-inline: 2px 0;
}
@keyframes gradient-15187afc {
0% {
    background-position: 0% 50%;
}
50% {
    background-position: 100% 50%;
}
100% {
    background-position: 0% 50%;
}
}
.loading-row .row-checkbox[data-v-15187afc] {
  text-align: center !important;
}
.loading-row span[data-v-15187afc] {
  display: inline-block;
  height: 24px;
  background: linear-gradient(to right, var(--color-background-darker), var(--color-text-maxcontrast), var(--color-background-darker));
  background-size: 600px 100%;
  border-radius: var(--border-radius);
  animation: gradient-15187afc 12s ease infinite;
}
.loading-row .row-wrapper[data-v-15187afc] {
  display: inline-flex;
  align-items: center;
}
.loading-row .row-checkbox span[data-v-15187afc] {
  width: 24px;
}
.loading-row .row-name span[data-v-15187afc]:last-of-type {
  margin-inline-start: 6px;
  width: 130px;
}
.loading-row .row-size span[data-v-15187afc] {
  width: 80px;
}
.loading-row .row-modified span[data-v-15187afc] {
  width: 90px;
}/*!
 * SPDX-FileCopyrightText: 2023-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
tr.file-picker__row[data-v-cb12dccb] {
  height: var(--row-height, 50px);
}
tr.file-picker__row td[data-v-cb12dccb] {
  cursor: pointer;
  overflow: hidden;
  text-overflow: ellipsis;
  border-bottom: none;
}
tr.file-picker__row td.row-checkbox[data-v-cb12dccb] {
  padding: 0 2px;
}
tr.file-picker__row td[data-v-cb12dccb]:not(.row-checkbox) {
  padding-inline: 14px 0;
}
tr.file-picker__row td.row-size[data-v-cb12dccb] {
  text-align: end;
  padding-inline: 0 14px;
}
tr.file-picker__row td.row-name[data-v-cb12dccb] {
  padding-inline: 2px 0;
}
.file-picker__row--selected[data-v-cb12dccb] {
  background-color: var(--color-background-dark);
}
.file-picker__row[data-v-cb12dccb]:hover {
  background-color: var(--color-background-hover);
}
.file-picker__name-container[data-v-cb12dccb] {
  display: flex;
  justify-content: start;
  align-items: center;
  height: 100%;
}
.file-picker__file-name[data-v-cb12dccb] {
  padding-inline-start: 6px;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
}
.file-picker__file-extension[data-v-cb12dccb] {
  color: var(--color-text-maxcontrast);
  min-width: fit-content;
}.file-picker__header-preview[data-v-006fdbd0] {
  width: 22px;
  height: 32px;
  flex: 0 0 auto;
}
.file-picker__files[data-v-006fdbd0] {
  margin: 2px;
  margin-inline-start: 12px;
  overflow: scroll auto;
}
.file-picker__files table[data-v-006fdbd0] {
  width: 100%;
  max-height: 100%;
  table-layout: fixed;
}
.file-picker__files th[data-v-006fdbd0] {
  position: sticky;
  z-index: 1;
  top: 0;
  background-color: var(--color-main-background);
  padding: 2px;
}
.file-picker__files th .header-wrapper[data-v-006fdbd0] {
  display: flex;
}
.file-picker__files th.row-checkbox[data-v-006fdbd0] {
  width: 44px;
}
.file-picker__files th.row-name[data-v-006fdbd0] {
  width: 230px;
}
.file-picker__files th.row-size[data-v-006fdbd0] {
  width: 100px;
}
.file-picker__files th.row-modified[data-v-006fdbd0] {
  width: 120px;
}
.file-picker__files th[data-v-006fdbd0]:not(.row-size) .button-vue__wrapper {
  justify-content: start;
  flex-direction: row-reverse;
}
.file-picker__files th[data-v-006fdbd0]:not(.row-size) .button-vue {
  padding-inline: 16px 4px;
}
.file-picker__files th.row-size[data-v-006fdbd0] .button-vue__wrapper {
  justify-content: end;
}
.file-picker__files th[data-v-006fdbd0] .button-vue__wrapper {
  color: var(--color-text-maxcontrast);
}
.file-picker__files th[data-v-006fdbd0] .button-vue__wrapper .button-vue__text {
  font-weight: normal;
}.file-picker__breadcrumbs[data-v-b357227a] {
  flex-grow: 0 !important;
}.file-picker__side[data-v-b42054b8] {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 0.5rem;
  min-width: 200px;
  padding: 2px;
  margin-block-start: 7px;
  overflow: auto;
}
.file-picker__side[data-v-b42054b8] .button-vue__wrapper {
  justify-content: start;
}
.file-picker__filter-input[data-v-b42054b8] {
  margin-block: 7px;
  max-width: 260px;
}
@media (max-width: 736px) {
.file-picker__side[data-v-b42054b8] {
    flex-direction: row;
    min-width: unset;
}
}
@media (max-width: 512px) {
.file-picker__side[data-v-b42054b8] {
    flex-direction: row;
    min-width: unset;
}
.file-picker__filter-input[data-v-b42054b8] {
    max-width: unset;
}
}/* Ensure focus outline is visible */
.file-picker__navigation {
  padding-inline: 8px 2px;
}
.file-picker__navigation, .file-picker__navigation * {
  box-sizing: border-box;
}
.file-picker__navigation .v-select.select {
  min-width: 220px;
}
@media (min-width: 513px) and (max-width: 736px) {
.file-picker__navigation {
    gap: 11px;
}
}
@media (max-width: 512px) {
.file-picker__navigation {
    flex-direction: column-reverse !important;
}
}.file-picker__view[data-v-20b719ba] {
  height: 50px;
  display: flex;
  justify-content: start;
  align-items: center;
}
.file-picker__view h3[data-v-20b719ba] {
  font-weight: bold;
  height: fit-content;
  margin: 0;
}
.file-picker__main[data-v-20b719ba] {
  box-sizing: border-box;
  width: 100%;
  display: flex;
  flex-direction: column;
  min-height: 0;
  flex: 1;
  padding-inline: 2px;
}
.file-picker__main *[data-v-20b719ba] {
  box-sizing: border-box;
}
[data-v-20b719ba] .file-picker {
  height: min(80vh, 800px) !important;
}
@media (max-width: 512px) {
[data-v-20b719ba] .file-picker {
    height: calc(100% - 16px - var(--default-clickable-area)) !important;
}
}
[data-v-20b719ba] .file-picker__content {
  display: flex;
  flex-direction: column;
  overflow: hidden;
}`],sourceRoot:""}]);const D=$},9137:(C,w,m)=>{m.d(w,{Z:()=>N});var B=m(7537),g=m.n(B),v=m(3645),l=m.n(v),x=l()(g());x.push([C.id,".notifications-button .notification__dot{fill:#ff4402}.notifications-button .notification__dot--warning{fill:var(--color-warning)}.notifications-button .notification__dot--white{fill:var(--color-primary-text)}.notifications-button.hasNotifications{animation-name:pulse;animation-duration:1600ms;animation-iteration-count:4}.notifications-button.hasNotifications svg{opacity:1}.notifications-button *{cursor:pointer}@keyframes pulse{0%{opacity:1}60%{opacity:.85}100%{opacity:1}}.notification-container .notification-wrapper{display:flex;flex-direction:column}.notification-container .dismiss-all{display:flex;justify-content:center;color:var(--color-text-maxcontrast);border-top:1px solid var(--color-border);padding:10px;background-color:var(--color-main-background)}.notification-container:after{right:101px}.notification{padding-bottom:12px}.notification:not(:last-child){border-bottom:1px solid var(--color-border)}.notification .notification-heading{display:flex;align-items:center;min-height:26px}.notification .notification-heading .notification-time{color:var(--color-text-maxcontrast);margin:13px 0 13px auto}.notification .notification-heading .notification-dismiss-button{margin:6px}.notification .notification-subject,.notification .notification-message,.notification .notification-full-message,.notification .notification-actions{margin:0 12px 12px}.notification .notification-subject{display:flex;align-items:center}.notification .notification-subject>.image{align-self:flex-start}.notification .notification-subject>span.subject,.notification .notification-subject>a>span.subject,.notification .notification-subject>.rich-text--wrapper,.notification .notification-subject>a>.rich-text--wrapper{padding-left:10px;word-wrap:anywhere}.notification .notification-message,.notification .notification-full-message{padding-left:42px;color:var(--color-text-maxcontrast)}.notification .notification-message>.collapsed,.notification .notification-full-message>.collapsed{overflow:hidden;max-height:70px}.notification .notification-message>.notification-overflow,.notification .notification-full-message>.notification-overflow{box-shadow:0 0 20px 20px var(--color-main-background);position:relative}.notification strong{font-weight:bold;opacity:1}.notification .notification-actions{overflow:hidden}.notification .notification-actions .button-vue{line-height:normal;margin:2px 8px}.notification .notification-actions:first-child{margin-left:auto}","",{version:3,sources:["webpack://./src/styles/styles.scss"],names:[],mappings:"AAMC,yCACC,YAAA,CACA,kDACC,yBAAA,CAED,gDACC,8BAAA,CAIF,uCACC,oBAAA,CACA,yBAAA,CACA,2BAAA,CAEA,2CACC,SAAA,CAIF,wBACC,cAAA,CAKD,iBACC,GACC,SAAA,CAGD,IACC,WAAA,CAGD,KACC,SAAA,CAAA,CAMF,8CACC,YAAA,CACA,qBAAA,CAGD,qCACC,YAAA,CACA,sBAAA,CACA,mCAAA,CACA,wCAAA,CACA,YAAA,CACA,6CAAA,CAID,8BACC,WAAA,CAKF,cACC,mBAAA,CAEA,+BACC,2CAAA,CAGD,oCACC,YAAA,CACA,kBAAA,CACA,eAAA,CAEA,uDACC,mCAAA,CACA,uBAAA,CAGD,iEACC,UAAA,CAIF,qJAIC,kBAAA,CAGD,oCACC,YAAA,CACA,kBAAA,CAEA,2CACC,qBAAA,CAGD,sNAIC,iBAAA,CACA,kBAAA,CAIF,6EAEC,iBAAA,CACA,mCAAA,CAEA,mGACC,eAAA,CACA,eAAA,CAGD,2HACC,qDAAA,CACA,iBAAA,CAIF,qBACC,gBAAA,CACA,SAAA,CAGD,oCACC,eAAA,CAEA,gDACC,kBAAA,CACA,cAAA,CAGD,gDACC,gBAAA",sourcesContent:[`/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

.notifications-button {
	.notification__dot {
		fill: #ff4402;
		&--warning {
			fill: var(--color-warning);
		}
		&--white {
			fill: var(--color-primary-text);
		}
	}

	&.hasNotifications {
		animation-name: pulse;
		animation-duration: 1600ms;
		animation-iteration-count: 4;

		svg {
			opacity: 1;
		}
	}

	* {
		cursor: pointer;
	}
}

svg {
	@keyframes pulse {
		0% {
			opacity: 1;
		}

		60% {
			opacity: .85;
		}

		100% {
			opacity: 1;
		}
	}
}

.notification-container {
	.notification-wrapper {
		display: flex;
		flex-direction: column;
	}

	.dismiss-all {
		display: flex;
		justify-content: center;
		color: var(--color-text-maxcontrast);
		border-top: 1px solid var(--color-border);
		padding: 10px;
		background-color: var(--color-main-background);
	}

	/* Menu arrow */
	&:after {
		right: 101px;
	}
}


.notification {
	padding-bottom: 12px;

	&:not(:last-child) {
		border-bottom: 1px solid var(--color-border);
	}

	.notification-heading {
		display: flex;
		align-items: center; // Else children will stretch in height as container is absolutely-positioned.
		min-height: 26px;

		.notification-time {
			color: var(--color-text-maxcontrast);
			margin: 13px 0 13px auto;
		}

		.notification-dismiss-button {
			margin: 6px;
		}
	}

	.notification-subject,
	.notification-message,
	.notification-full-message,
	.notification-actions {
		margin: 0 12px 12px;
	}

	.notification-subject {
		display: flex;
		align-items: center;

		& > .image {
			align-self: flex-start;
		}

		& > span.subject,
		& > a > span.subject,
		& > .rich-text--wrapper,
		& > a > .rich-text--wrapper {
			padding-left: 10px;
			word-wrap: anywhere;
		}
	}

	.notification-message,
	.notification-full-message {
		padding-left: 42px; // 32px icon + 10px title padding
		color: var(--color-text-maxcontrast);

		& > .collapsed {
			overflow: hidden;
			max-height: 70px;
		}

		& > .notification-overflow {
			box-shadow: 0 0 20px 20px var(--color-main-background);
			position: relative;
		}
	}

	strong {
		font-weight: bold;
		opacity: 1;
	}

	.notification-actions {
		overflow: hidden;

		.button-vue {
			line-height: normal;
			margin: 2px 8px;
		}

		&:first-child {
			margin-left: auto;
		}
	}
}
`],sourceRoot:""}]);const N=x},3645:C=>{C.exports=function(w){var m=[];return m.toString=function(){return this.map(function(g){var v="",l=typeof g[5]<"u";return g[4]&&(v+="@supports (".concat(g[4],") {")),g[2]&&(v+="@media ".concat(g[2]," {")),l&&(v+="@layer".concat(g[5].length>0?" ".concat(g[5]):""," {")),v+=w(g),l&&(v+="}"),g[2]&&(v+="}"),g[4]&&(v+="}"),v}).join("")},m.i=function(g,v,l,x,N){typeof g=="string"&&(g=[[null,g,void 0]]);var I={};if(l)for(var y=0;y<this.length;y++){var $=this[y][0];$!=null&&(I[$]=!0)}for(var Z=0;Z<g.length;Z++){var P=[].concat(g[Z]);l&&I[P[0]]||(typeof N<"u"&&(typeof P[5]>"u"||(P[1]="@layer".concat(P[5].length>0?" ".concat(P[5]):""," {").concat(P[1],"}")),P[5]=N),v&&(P[2]&&(P[1]="@media ".concat(P[2]," {").concat(P[1],"}")),P[2]=v),x&&(P[4]?(P[1]="@supports (".concat(P[4],") {").concat(P[1],"}"),P[4]=x):P[4]="".concat(x)),m.push(P))}},m}},1667:C=>{C.exports=function(w,m){return m||(m={}),w&&(w=String(w.__esModule?w.default:w),/^['"].*['"]$/.test(w)&&(w=w.slice(1,-1)),m.hash&&(w+=m.hash),/["'() \t\n]|(%20)/.test(w)||m.needQuotes?'"'.concat(w.replace(/"/g,'\\"').replace(/\n/g,"\\n"),'"'):w)}},7537:C=>{C.exports=function(w){var m=w[1],B=w[3];if(!B)return m;if(typeof btoa=="function"){var g=btoa(unescape(encodeURIComponent(JSON.stringify(B)))),v="sourceMappingURL=data:application/json;charset=utf-8;base64,".concat(g),l="/*# ".concat(v," */");return[m].concat([l]).join(`
`)}return[m].join(`
`)}},3379:C=>{var w=[];function m(v){for(var l=-1,x=0;x<w.length;x++)if(w[x].identifier===v){l=x;break}return l}function B(v,l){for(var x={},N=[],I=0;I<v.length;I++){var y=v[I],$=l.base?y[0]+l.base:y[0],Z=x[$]||0,P="".concat($," ").concat(Z);x[$]=Z+1;var D=m(P),he={css:y[1],media:y[2],sourceMap:y[3],supports:y[4],layer:y[5]};if(D!==-1)w[D].references++,w[D].updater(he);else{var ge=g(he,l);l.byIndex=I,w.splice(I,0,{identifier:P,updater:ge,references:1})}N.push(P)}return N}function g(v,l){var x=l.domAPI(l);x.update(v);var N=function(y){if(y){if(y.css===v.css&&y.media===v.media&&y.sourceMap===v.sourceMap&&y.supports===v.supports&&y.layer===v.layer)return;x.update(v=y)}else x.remove()};return N}C.exports=function(v,l){l=l||{},v=v||[];var x=B(v,l);return function(I){I=I||[];for(var y=0;y<x.length;y++){var $=x[y],Z=m($);w[Z].references--}for(var P=B(I,l),D=0;D<x.length;D++){var he=x[D],ge=m(he);w[ge].references===0&&(w[ge].updater(),w.splice(ge,1))}x=P}}},569:C=>{var w={};function m(g){if(typeof w[g]>"u"){var v=document.querySelector(g);if(window.HTMLIFrameElement&&v instanceof window.HTMLIFrameElement)try{v=v.contentDocument.head}catch{v=null}w[g]=v}return w[g]}function B(g,v){var l=m(g);if(!l)throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");l.appendChild(v)}C.exports=B},9216:C=>{function w(m){var B=document.createElement("style");return m.setAttributes(B,m.attributes),m.insert(B,m.options),B}C.exports=w},3565:(C,w,m)=>{function B(g){var v=m.nc;v&&g.setAttribute("nonce",v)}C.exports=B},7795:C=>{function w(g,v,l){var x="";l.supports&&(x+="@supports (".concat(l.supports,") {")),l.media&&(x+="@media ".concat(l.media," {"));var N=typeof l.layer<"u";N&&(x+="@layer".concat(l.layer.length>0?" ".concat(l.layer):""," {")),x+=l.css,N&&(x+="}"),l.media&&(x+="}"),l.supports&&(x+="}");var I=l.sourceMap;I&&typeof btoa<"u"&&(x+=`
/*# sourceMappingURL=data:application/json;base64,`.concat(btoa(unescape(encodeURIComponent(JSON.stringify(I))))," */")),v.styleTagTransform(x,g,v.options)}function m(g){if(g.parentNode===null)return!1;g.parentNode.removeChild(g)}function B(g){if(typeof document>"u")return{update:function(){},remove:function(){}};var v=g.insertStyleElement(g);return{update:function(x){w(v,g,x)},remove:function(){m(v)}}}C.exports=B},4589:C=>{function w(m,B){if(B.styleSheet)B.styleSheet.cssText=m;else{for(;B.firstChild;)B.removeChild(B.firstChild);B.appendChild(document.createTextNode(m))}}C.exports=w},144:(C,w,m)=>{m.d(w,{$y:()=>Ue,Ah:()=>xo,BK:()=>$a,EB:()=>Ua,FN:()=>ba,Fl:()=>Ma,OT:()=>Tr,RC:()=>Co,Rr:()=>so,SU:()=>Ta,Vh:()=>Or,XI:()=>Oa,Y3:()=>Wt,YP:()=>Fa,ZM:()=>Pa,ZP:()=>J,aZ:()=>Bo,bv:()=>yo,dq:()=>te,h:()=>ho,iH:()=>Sa,m0:()=>La,nZ:()=>Mr,qj:()=>Ba,sj:()=>mo,t8:()=>Nt});/*!
 * Vue.js v2.7.16
 * (c) 2014-2023 Evan You
 * Released under the MIT License.
 */var B=Object.freeze({}),g=Array.isArray;function v(e){return e==null}function l(e){return e!=null}function x(e){return e===!0}function N(e){return e===!1}function I(e){return typeof e=="string"||typeof e=="number"||typeof e=="symbol"||typeof e=="boolean"}function y(e){return typeof e=="function"}function $(e){return e!==null&&typeof e=="object"}var Z=Object.prototype.toString;function P(e){return Z.call(e).slice(8,-1)}function D(e){return Z.call(e)==="[object Object]"}function he(e){return Z.call(e)==="[object RegExp]"}function ge(e){var r=parseFloat(String(e));return r>=0&&Math.floor(r)===r&&isFinite(e)}function ke(e){return l(e)&&typeof e.then=="function"&&typeof e.catch=="function"}function Se(e){return e==null?"":Array.isArray(e)||D(e)&&e.toString===Z?JSON.stringify(e,or,2):String(e)}function or(e,r){return r&&r.__v_isRef?r.value:r}function Je(e){var r=parseFloat(e);return isNaN(r)?e:r}function de(e,r){for(var i=Object.create(null),a=e.split(","),o=0;o<a.length;o++)i[a[o]]=!0;return r?function(s){return i[s.toLowerCase()]}:function(s){return i[s]}}var on=de("slot,component",!0),ua=de("key,ref,slot,slot-scope,is");function Oe(e,r){var i=e.length;if(i){if(r===e[i-1]){e.length=i-1;return}var a=e.indexOf(r);if(a>-1)return e.splice(a,1)}}var da=Object.prototype.hasOwnProperty;function oe(e,r){return da.call(e,r)}function Le(e){var r=Object.create(null);return function(a){var o=r[a];return o||(r[a]=e(a))}}var pa=/-(\w)/g,Re=Le(function(e){return e.replace(pa,function(r,i){return i?i.toUpperCase():""})}),sr=Le(function(e){return e.charAt(0).toUpperCase()+e.slice(1)}),Aa=/\B([A-Z])/g,ut=Le(function(e){return e.replace(Aa,"-$1").toLowerCase()});function va(e,r){function i(a){var o=arguments.length;return o?o>1?e.apply(r,arguments):e.call(r,a):e.call(r)}return i._length=e.length,i}function ha(e,r){return e.bind(r)}var fr=Function.prototype.bind?ha:va;function sn(e,r){r=r||0;for(var i=e.length-r,a=new Array(i);i--;)a[i]=e[i+r];return a}function Y(e,r){for(var i in r)e[i]=r[i];return e}function cr(e){for(var r={},i=0;i<e.length;i++)e[i]&&Y(r,e[i]);return r}function X(e,r,i){}var kt=function(e,r,i){return!1},lr=function(e){return e};function Fe(e,r){if(e===r)return!0;var i=$(e),a=$(r);if(i&&a)try{var o=Array.isArray(e),s=Array.isArray(r);if(o&&s)return e.length===r.length&&e.every(function(u,A){return Fe(u,r[A])});if(e instanceof Date&&r instanceof Date)return e.getTime()===r.getTime();if(!o&&!s){var f=Object.keys(e),c=Object.keys(r);return f.length===c.length&&f.every(function(u){return Fe(e[u],r[u])})}else return!1}catch{return!1}else return!i&&!a?String(e)===String(r):!1}function ur(e,r){for(var i=0;i<e.length;i++)if(Fe(e[i],r))return i;return-1}function St(e){var r=!1;return function(){r||(r=!0,e.apply(this,arguments))}}function fn(e,r){return e===r?e===0&&1/e!==1/r:e===e||r===r}var dr="data-server-rendered",Ot=["component","directive","filter"],pr=["beforeCreate","created","beforeMount","mounted","beforeUpdate","updated","beforeDestroy","destroyed","activated","deactivated","errorCaptured","serverPrefetch","renderTracked","renderTriggered"],re={optionMergeStrategies:Object.create(null),silent:!1,productionTip:!1,devtools:!1,performance:!1,errorHandler:null,warnHandler:null,ignoredElements:[],keyCodes:Object.create(null),isReservedTag:kt,isReservedAttr:kt,isUnknownElement:kt,getTagNamespace:X,parsePlatformTagName:lr,mustUseProp:kt,async:!0,_lifecycleHooks:pr},Ar=/a-zA-Z\u00B7\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u037D\u037F-\u1FFF\u200C-\u200D\u203F-\u2040\u2070-\u218F\u2C00-\u2FEF\u3001-\uD7FF\uF900-\uFDCF\uFDF0-\uFFFD/;function vr(e){var r=(e+"").charCodeAt(0);return r===36||r===95}function q(e,r,i,a){Object.defineProperty(e,r,{value:i,enumerable:!!a,writable:!0,configurable:!0})}var ga=new RegExp("[^".concat(Ar.source,".$_\\d]"));function _a(e){if(!ga.test(e)){var r=e.split(".");return function(i){for(var a=0;a<r.length;a++){if(!i)return;i=i[r[a]]}return i}}}var ma="__proto__"in{},se=typeof window<"u",ie=se&&window.navigator.userAgent.toLowerCase(),Qe=ie&&/msie|trident/.test(ie),Ve=ie&&ie.indexOf("msie 9.0")>0,cn=ie&&ie.indexOf("edge/")>0;ie&&ie.indexOf("android")>0;var Ca=ie&&/iphone|ipad|ipod|ios/.test(ie);ie&&/chrome\/\d+/.test(ie),ie&&/phantomjs/.test(ie);var hr=ie&&ie.match(/firefox\/(\d+)/),ln={}.watch,gr=!1;if(se)try{var _r={};Object.defineProperty(_r,"passive",{get:function(){gr=!0}}),window.addEventListener("test-passive",null,_r)}catch{}var Tt,Te=function(){return Tt===void 0&&(!se&&typeof m.g<"u"?Tt=m.g.process&&m.g.process.env.VUE_ENV==="server":Tt=!1),Tt},Pt=se&&window.__VUE_DEVTOOLS_GLOBAL_HOOK__;function et(e){return typeof e=="function"&&/native code/.test(e.toString())}var dt=typeof Symbol<"u"&&et(Symbol)&&typeof Reflect<"u"&&et(Reflect.ownKeys),pt;typeof Set<"u"&&et(Set)?pt=Set:pt=function(){function e(){this.set=Object.create(null)}return e.prototype.has=function(r){return this.set[r]===!0},e.prototype.add=function(r){this.set[r]=!0},e.prototype.clear=function(){this.set=Object.create(null)},e}();var K=null;function ba(){return K&&{proxy:K}}function Pe(e){e===void 0&&(e=null),e||K&&K._scope.off(),K=e,e&&e._scope.on()}var fe=function(){function e(r,i,a,o,s,f,c,u){this.tag=r,this.data=i,this.children=a,this.text=o,this.elm=s,this.ns=void 0,this.context=f,this.fnContext=void 0,this.fnOptions=void 0,this.fnScopeId=void 0,this.key=i&&i.key,this.componentOptions=c,this.componentInstance=void 0,this.parent=void 0,this.raw=!1,this.isStatic=!1,this.isRootInsert=!0,this.isComment=!1,this.isCloned=!1,this.isOnce=!1,this.asyncFactory=u,this.asyncMeta=void 0,this.isAsyncPlaceholder=!1}return Object.defineProperty(e.prototype,"child",{get:function(){return this.componentInstance},enumerable:!1,configurable:!0}),e}(),ze=function(e){e===void 0&&(e="");var r=new fe;return r.text=e,r.isComment=!0,r};function tt(e){return new fe(void 0,void 0,void 0,String(e))}function un(e){var r=new fe(e.tag,e.data,e.children&&e.children.slice(),e.text,e.elm,e.context,e.componentOptions,e.asyncFactory);return r.ns=e.ns,r.isStatic=e.isStatic,r.key=e.key,r.isComment=e.isComment,r.fnContext=e.fnContext,r.fnOptions=e.fnOptions,r.fnScopeId=e.fnScopeId,r.asyncMeta=e.asyncMeta,r.isCloned=!0,r}var mr=function(){return mr=Object.assign||function(r){for(var i,a=1,o=arguments.length;a<o;a++){i=arguments[a];for(var s in i)Object.prototype.hasOwnProperty.call(i,s)&&(r[s]=i[s])}return r},mr.apply(this,arguments)};typeof SuppressedError=="function"&&SuppressedError;var ya=0,$t=[],xa=function(){for(var e=0;e<$t.length;e++){var r=$t[e];r.subs=r.subs.filter(function(i){return i}),r._pending=!1}$t.length=0},me=function(){function e(){this._pending=!1,this.id=ya++,this.subs=[]}return e.prototype.addSub=function(r){this.subs.push(r)},e.prototype.removeSub=function(r){this.subs[this.subs.indexOf(r)]=null,this._pending||(this._pending=!0,$t.push(this))},e.prototype.depend=function(r){e.target&&e.target.addDep(this)},e.prototype.notify=function(r){for(var i=this.subs.filter(function(f){return f}),a=0,o=i.length;a<o;a++){var s=i[a];s.update()}},e}();me.target=null;var It=[];function nt(e){It.push(e),me.target=e}function rt(){It.pop(),me.target=It[It.length-1]}var Cr=Array.prototype,Dt=Object.create(Cr),wa=["push","pop","shift","unshift","splice","sort","reverse"];wa.forEach(function(e){var r=Cr[e];q(Dt,e,function(){for(var a=[],o=0;o<arguments.length;o++)a[o]=arguments[o];var s=r.apply(this,a),f=this.__ob__,c;switch(e){case"push":case"unshift":c=a;break;case"splice":c=a.slice(2);break}return c&&f.observeArray(c),f.dep.notify(),s})});var br=Object.getOwnPropertyNames(Dt),yr={},dn=!0;function $e(e){dn=e}var Ea={notify:X,depend:X,addSub:X,removeSub:X},xr=function(){function e(r,i,a){if(i===void 0&&(i=!1),a===void 0&&(a=!1),this.value=r,this.shallow=i,this.mock=a,this.dep=a?Ea:new me,this.vmCount=0,q(r,"__ob__",this),g(r)){if(!a)if(ma)r.__proto__=Dt;else for(var o=0,s=br.length;o<s;o++){var f=br[o];q(r,f,Dt[f])}i||this.observeArray(r)}else for(var c=Object.keys(r),o=0;o<c.length;o++){var f=c[o];Ie(r,f,yr,void 0,i,a)}}return e.prototype.observeArray=function(r){for(var i=0,a=r.length;i<a;i++)ye(r[i],!1,this.mock)},e}();function ye(e,r,i){if(e&&oe(e,"__ob__")&&e.__ob__ instanceof xr)return e.__ob__;if(dn&&(i||!Te())&&(g(e)||D(e))&&Object.isExtensible(e)&&!e.__v_skip&&!te(e)&&!(e instanceof fe))return new xr(e,r,i)}function Ie(e,r,i,a,o,s,f){f===void 0&&(f=!1);var c=new me,u=Object.getOwnPropertyDescriptor(e,r);if(!(u&&u.configurable===!1)){var A=u&&u.get,_=u&&u.set;(!A||_)&&(i===yr||arguments.length===2)&&(i=e[r]);var b=o?i&&i.__ob__:ye(i,!1,s);return Object.defineProperty(e,r,{enumerable:!0,configurable:!0,get:function(){var k=A?A.call(e):i;return me.target&&(c.depend(),b&&(b.dep.depend(),g(k)&&Er(k))),te(k)&&!o?k.value:k},set:function(k){var z=A?A.call(e):i;if(fn(z,k)){if(_)_.call(e,k);else{if(A)return;if(!o&&te(z)&&!te(k)){z.value=k;return}else i=k}b=o?k&&k.__ob__:ye(k,!1,s),c.notify()}}}),c}}function Nt(e,r,i){if(!Ue(e)){var a=e.__ob__;return g(e)&&ge(r)?(e.length=Math.max(e.length,r),e.splice(r,1,i),a&&!a.shallow&&a.mock&&ye(i,!1,!0),i):r in e&&!(r in Object.prototype)?(e[r]=i,i):e._isVue||a&&a.vmCount?i:a?(Ie(a.value,r,i,void 0,a.shallow,a.mock),a.dep.notify(),i):(e[r]=i,i)}}function wr(e,r){if(g(e)&&ge(r)){e.splice(r,1);return}var i=e.__ob__;e._isVue||i&&i.vmCount||Ue(e)||oe(e,r)&&(delete e[r],i&&i.dep.notify())}function Er(e){for(var r=void 0,i=0,a=e.length;i<a;i++)r=e[i],r&&r.__ob__&&r.__ob__.dep.depend(),g(r)&&Er(r)}function Ba(e){return kr(e,!1),e}function Br(e){return kr(e,!0),q(e,"__v_isShallow",!0),e}function kr(e,r){if(!Ue(e)){if(0)var i;var a=ye(e,r,Te())}}function it(e){return Ue(e)?it(e.__v_raw):!!(e&&e.__ob__)}function pn(e){return!!(e&&e.__v_isShallow)}function Ue(e){return!!(e&&e.__v_isReadonly)}function Jf(e){return it(e)||Ue(e)}function ka(e){var r=e&&e.__v_raw;return r?ka(r):e}function Qf(e){return Object.isExtensible(e)&&q(e,"__v_skip",!0),e}function Vf(e){var r=P(e);return r==="Map"||r==="WeakMap"||r==="Set"||r==="WeakSet"}var At="__v_isRef";function te(e){return!!(e&&e.__v_isRef===!0)}function Sa(e){return Sr(e,!1)}function Oa(e){return Sr(e,!0)}function Sr(e,r){if(te(e))return e;var i={};return q(i,At,!0),q(i,"__v_isShallow",r),q(i,"dep",Ie(i,"value",e,null,r,Te())),i}function ec(e){e.dep&&e.dep.notify()}function Ta(e){return te(e)?e.value:e}function tc(e){if(it(e))return e;for(var r={},i=Object.keys(e),a=0;a<i.length;a++)Mt(r,e,i[a]);return r}function Mt(e,r,i){Object.defineProperty(e,i,{enumerable:!0,configurable:!0,get:function(){var a=r[i];if(te(a))return a.value;var o=a&&a.__ob__;return o&&o.dep.depend(),a},set:function(a){var o=r[i];te(o)&&!te(a)?o.value=a:r[i]=a}})}function Pa(e){var r=new me,i=e(function(){r.depend()},function(){r.notify()}),a=i.get,o=i.set,s={get value(){return a()},set value(f){o(f)}};return q(s,At,!0),s}function $a(e){var r=g(e)?new Array(e.length):{};for(var i in e)r[i]=Or(e,i);return r}function Or(e,r,i){var a=e[r];if(te(a))return a;var o={get value(){var s=e[r];return s===void 0?i:s},set value(s){e[r]=s}};return q(o,At,!0),o}var Ia="__v_rawToReadonly",Da="__v_rawToShallowReadonly";function Tr(e){return Pr(e,!1)}function Pr(e,r){if(!D(e)||Ue(e))return e;var i=r?Da:Ia,a=e[i];if(a)return a;var o=Object.create(Object.getPrototypeOf(e));q(e,i,o),q(o,"__v_isReadonly",!0),q(o,"__v_raw",e),te(e)&&q(o,At,!0),(r||pn(e))&&q(o,"__v_isShallow",!0);for(var s=Object.keys(e),f=0;f<s.length;f++)Na(o,e,s[f],r);return o}function Na(e,r,i,a){Object.defineProperty(e,i,{enumerable:!0,configurable:!0,get:function(){var o=r[i];return a||!D(o)?o:Tr(o)},set:function(){}})}function nc(e){return Pr(e,!0)}function Ma(e,r){var i,a,o=y(e);o?(i=e,a=X):(i=e.get,a=e.set);var s=Te()?null:new Ct(K,i,X,{lazy:!0}),f={effect:s,get value(){return s?(s.dirty&&s.evaluate(),me.target&&s.depend(),s.value):i()},set value(c){a(c)}};return q(f,At,!0),q(f,"__v_isReadonly",o),f}var jt="watcher",$r="".concat(jt," callback"),Ir="".concat(jt," getter"),ja="".concat(jt," cleanup");function La(e,r){return Lt(e,null,r)}function Ra(e,r){return Lt(e,null,{flush:"post"})}function rc(e,r){return Lt(e,null,{flush:"sync"})}var Dr={};function Fa(e,r,i){return Lt(e,r,i)}function Lt(e,r,i){var a=i===void 0?B:i,o=a.immediate,s=a.deep,f=a.flush,c=f===void 0?"pre":f,u=a.onTrack,A=a.onTrigger,_=function(R){Ae("Invalid watch source: ".concat(R,". A watch source can only be a getter/effect ")+"function, a ref, a reactive object, or an array of these types.")},b=K,O=function(R,ue,ne){ne===void 0&&(ne=null);var be=xe(R,null,ne,b,ue);return s&&be&&be.__ob__&&be.__ob__.dep.depend(),be},k,z=!1,M=!1;if(te(e)?(k=function(){return e.value},z=pn(e)):it(e)?(k=function(){return e.__ob__.dep.depend(),e},s=!0):g(e)?(M=!0,z=e.some(function(R){return it(R)||pn(R)}),k=function(){return e.map(function(R){if(te(R))return R.value;if(it(R))return R.__ob__.dep.depend(),at(R);if(y(R))return O(R,Ir)})}):y(e)?r?k=function(){return O(e,Ir)}:k=function(){if(!(b&&b._isDestroyed))return V&&V(),O(e,jt,[Q])}:k=X,r&&s){var ce=k;k=function(){return at(ce())}}var V,Q=function(R){V=U.onStop=function(){O(R,ja)}};if(Te())return Q=X,r?o&&O(r,$r,[k(),M?[]:void 0,Q]):k(),X;var U=new Ct(K,k,X,{lazy:!0});U.noRecurse=!r;var le=M?[]:Dr;return U.run=function(){if(U.active)if(r){var R=U.get();(s||z||(M?R.some(function(ue,ne){return fn(ue,le[ne])}):fn(R,le)))&&(V&&V(),O(r,$r,[R,le===Dr?void 0:le,Q]),le=R)}else U.get()},c==="sync"?U.update=U.run:c==="post"?(U.post=!0,U.update=function(){return Sn(U)}):U.update=function(){if(b&&b===K&&!b._isMounted){var R=b._preWatchers||(b._preWatchers=[]);R.indexOf(U)<0&&R.push(U)}else Sn(U)},r?o?U.run():le=U.get():c==="post"&&b?b.$once("hook:mounted",function(){return U.get()}):U.get(),function(){U.teardown()}}var ae,Nr=function(){function e(r){r===void 0&&(r=!1),this.detached=r,this.active=!0,this.effects=[],this.cleanups=[],this.parent=ae,!r&&ae&&(this.index=(ae.scopes||(ae.scopes=[])).push(this)-1)}return e.prototype.run=function(r){if(this.active){var i=ae;try{return ae=this,r()}finally{ae=i}}},e.prototype.on=function(){ae=this},e.prototype.off=function(){ae=this.parent},e.prototype.stop=function(r){if(this.active){var i=void 0,a=void 0;for(i=0,a=this.effects.length;i<a;i++)this.effects[i].teardown();for(i=0,a=this.cleanups.length;i<a;i++)this.cleanups[i]();if(this.scopes)for(i=0,a=this.scopes.length;i<a;i++)this.scopes[i].stop(!0);if(!this.detached&&this.parent&&!r){var o=this.parent.scopes.pop();o&&o!==this&&(this.parent.scopes[this.index]=o,o.index=this.index)}this.parent=void 0,this.active=!1}},e}();function ic(e){return new Nr(e)}function za(e,r){r===void 0&&(r=ae),r&&r.active&&r.effects.push(e)}function Mr(){return ae}function Ua(e){ae&&ae.cleanups.push(e)}function ac(e,r){K&&(jr(K)[e]=r)}function jr(e){var r=e._provided,i=e.$parent&&e.$parent._provided;return i===r?e._provided=Object.create(i):r}function oc(e,r,i){i===void 0&&(i=!1);var a=K;if(a){var o=a.$parent&&a.$parent._provided;if(o&&e in o)return o[e];if(arguments.length>1)return i&&y(r)?r.call(a):r}}var Lr=Le(function(e){var r=e.charAt(0)==="&";e=r?e.slice(1):e;var i=e.charAt(0)==="~";e=i?e.slice(1):e;var a=e.charAt(0)==="!";return e=a?e.slice(1):e,{name:e,once:i,capture:a,passive:r}});function An(e,r){function i(){var a=i.fns;if(g(a))for(var o=a.slice(),s=0;s<o.length;s++)xe(o[s],null,arguments,r,"v-on handler");else return xe(a,null,arguments,r,"v-on handler")}return i.fns=e,i}function Rr(e,r,i,a,o,s){var f,c,u,A;for(f in e)c=e[f],u=r[f],A=Lr(f),v(c)||(v(u)?(v(c.fns)&&(c=e[f]=An(c,s)),x(A.once)&&(c=e[f]=o(A.name,c,A.capture)),i(A.name,c,A.capture,A.passive,A.params)):c!==u&&(u.fns=c,e[f]=u));for(f in r)v(e[f])&&(A=Lr(f),a(A.name,r[f],A.capture))}function De(e,r,i){e instanceof fe&&(e=e.data.hook||(e.data.hook={}));var a,o=e[r];function s(){i.apply(this,arguments),Oe(a.fns,s)}v(o)?a=An([s]):l(o.fns)&&x(o.merged)?(a=o,a.fns.push(s)):a=An([o,s]),a.merged=!0,e[r]=a}function Wa(e,r,i){var a=r.options.props;if(!v(a)){var o={},s=e.attrs,f=e.props;if(l(s)||l(f))for(var c in a){var u=ut(c);if(0)var A;Fr(o,f,c,u,!0)||Fr(o,s,c,u,!1)}return o}}function Fr(e,r,i,a,o){if(l(r)){if(oe(r,i))return e[i]=r[i],o||delete r[i],!0;if(oe(r,a))return e[i]=r[a],o||delete r[a],!0}return!1}function Ha(e){for(var r=0;r<e.length;r++)if(g(e[r]))return Array.prototype.concat.apply([],e);return e}function vn(e){return I(e)?[tt(e)]:g(e)?zr(e):void 0}function vt(e){return l(e)&&l(e.text)&&N(e.isComment)}function zr(e,r){var i=[],a,o,s,f;for(a=0;a<e.length;a++)o=e[a],!(v(o)||typeof o=="boolean")&&(s=i.length-1,f=i[s],g(o)?o.length>0&&(o=zr(o,"".concat(r||"","_").concat(a)),vt(o[0])&&vt(f)&&(i[s]=tt(f.text+o[0].text),o.shift()),i.push.apply(i,o)):I(o)?vt(f)?i[s]=tt(f.text+o):o!==""&&i.push(tt(o)):vt(o)&&vt(f)?i[s]=tt(f.text+o.text):(x(e._isVList)&&l(o.tag)&&v(o.key)&&l(r)&&(o.key="__vlist".concat(r,"_").concat(a,"__")),i.push(o)));return i}function Ga(e,r){var i=null,a,o,s,f;if(g(e)||typeof e=="string")for(i=new Array(e.length),a=0,o=e.length;a<o;a++)i[a]=r(e[a],a);else if(typeof e=="number")for(i=new Array(e),a=0;a<e;a++)i[a]=r(a+1,a);else if($(e))if(dt&&e[Symbol.iterator]){i=[];for(var c=e[Symbol.iterator](),u=c.next();!u.done;)i.push(r(u.value,i.length)),u=c.next()}else for(s=Object.keys(e),i=new Array(s.length),a=0,o=s.length;a<o;a++)f=s[a],i[a]=r(e[f],f,a);return l(i)||(i=[]),i._isVList=!0,i}function Ya(e,r,i,a){var o=this.$scopedSlots[e],s;o?(i=i||{},a&&(i=Y(Y({},a),i)),s=o(i)||(y(r)?r():r)):s=this.$slots[e]||(y(r)?r():r);var f=i&&i.slot;return f?this.$createElement("template",{slot:f},s):s}function Za(e){return Zt(this.$options,"filters",e,!0)||lr}function Ur(e,r){return g(e)?e.indexOf(r)===-1:e!==r}function Xa(e,r,i,a,o){var s=re.keyCodes[r]||i;return o&&a&&!re.keyCodes[r]?Ur(o,a):s?Ur(s,e):a?ut(a)!==r:e===void 0}function Ka(e,r,i,a,o){if(i&&$(i)){g(i)&&(i=cr(i));var s=void 0,f=function(u){if(u==="class"||u==="style"||ua(u))s=e;else{var A=e.attrs&&e.attrs.type;s=a||re.mustUseProp(r,A,u)?e.domProps||(e.domProps={}):e.attrs||(e.attrs={})}var _=Re(u),b=ut(u);if(!(_ in s)&&!(b in s)&&(s[u]=i[u],o)){var O=e.on||(e.on={});O["update:".concat(u)]=function(k){i[u]=k}}};for(var c in i)f(c)}return e}function qa(e,r){var i=this._staticTrees||(this._staticTrees=[]),a=i[e];return a&&!r||(a=i[e]=this.$options.staticRenderFns[e].call(this._renderProxy,this._c,this),Wr(a,"__static__".concat(e),!1)),a}function Ja(e,r,i){return Wr(e,"__once__".concat(r).concat(i?"_".concat(i):""),!0),e}function Wr(e,r,i){if(g(e))for(var a=0;a<e.length;a++)e[a]&&typeof e[a]!="string"&&Hr(e[a],"".concat(r,"_").concat(a),i);else Hr(e,r,i)}function Hr(e,r,i){e.isStatic=!0,e.key=r,e.isOnce=i}function Qa(e,r){if(r&&D(r)){var i=e.on=e.on?Y({},e.on):{};for(var a in r){var o=i[a],s=r[a];i[a]=o?[].concat(o,s):s}}return e}function Gr(e,r,i,a){r=r||{$stable:!i};for(var o=0;o<e.length;o++){var s=e[o];g(s)?Gr(s,r,i):s&&(s.proxy&&(s.fn.proxy=!0),r[s.key]=s.fn)}return a&&(r.$key=a),r}function Va(e,r){for(var i=0;i<r.length;i+=2){var a=r[i];typeof a=="string"&&a&&(e[r[i]]=r[i+1])}return e}function eo(e,r){return typeof e=="string"?r+e:e}function Yr(e){e._o=Ja,e._n=Je,e._s=Se,e._l=Ga,e._t=Ya,e._q=Fe,e._i=ur,e._m=qa,e._f=Za,e._k=Xa,e._b=Ka,e._v=tt,e._e=ze,e._u=Gr,e._g=Qa,e._d=Va,e._p=eo}function hn(e,r){if(!e||!e.length)return{};for(var i={},a=0,o=e.length;a<o;a++){var s=e[a],f=s.data;if(f&&f.attrs&&f.attrs.slot&&delete f.attrs.slot,(s.context===r||s.fnContext===r)&&f&&f.slot!=null){var c=f.slot,u=i[c]||(i[c]=[]);s.tag==="template"?u.push.apply(u,s.children||[]):u.push(s)}else(i.default||(i.default=[])).push(s)}for(var A in i)i[A].every(to)&&delete i[A];return i}function to(e){return e.isComment&&!e.asyncFactory||e.text===" "}function ht(e){return e.isComment&&e.asyncFactory}function gt(e,r,i,a){var o,s=Object.keys(i).length>0,f=r?!!r.$stable:!s,c=r&&r.$key;if(!r)o={};else{if(r._normalized)return r._normalized;if(f&&a&&a!==B&&c===a.$key&&!s&&!a.$hasNormal)return a;o={};for(var u in r)r[u]&&u[0]!=="$"&&(o[u]=no(e,i,u,r[u]))}for(var A in i)A in o||(o[A]=ro(i,A));return r&&Object.isExtensible(r)&&(r._normalized=o),q(o,"$stable",f),q(o,"$key",c),q(o,"$hasNormal",s),o}function no(e,r,i,a){var o=function(){var s=K;Pe(e);var f=arguments.length?a.apply(null,arguments):a({});f=f&&typeof f=="object"&&!g(f)?[f]:vn(f);var c=f&&f[0];return Pe(s),f&&(!c||f.length===1&&c.isComment&&!ht(c))?void 0:f};return a.proxy&&Object.defineProperty(r,i,{get:o,enumerable:!0,configurable:!0}),o}function ro(e,r){return function(){return e[r]}}function io(e){var r=e.$options,i=r.setup;if(i){var a=e._setupContext=Zr(e);Pe(e),nt();var o=xe(i,null,[e._props||Br({}),a],e,"setup");if(rt(),Pe(),y(o))r.render=o;else if($(o))if(e._setupState=o,o.__sfc){var f=e._setupProxy={};for(var s in o)s!=="__sfc"&&Mt(f,o,s)}else for(var s in o)vr(s)||Mt(e,o,s)}}function Zr(e){var r=!1;return{get attrs(){if(!e._attrsProxy){var i=e._attrsProxy={};q(i,"_v_attr_proxy",!0),Rt(i,e.$attrs,B,e,"$attrs")}return e._attrsProxy},get listeners(){if(!e._listenersProxy){var i=e._listenersProxy={};Rt(i,e.$listeners,B,e,"$listeners")}return e._listenersProxy},get slots(){return oo(e)},emit:fr(e.$emit,e),expose:function(i){i&&Object.keys(i).forEach(function(a){return Mt(e,i,a)})}}}function Rt(e,r,i,a,o){var s=!1;for(var f in r)f in e?r[f]!==i[f]&&(s=!0):(s=!0,ao(e,f,a,o));for(var f in e)f in r||(s=!0,delete e[f]);return s}function ao(e,r,i,a){Object.defineProperty(e,r,{enumerable:!0,configurable:!0,get:function(){return i[a][r]}})}function oo(e){return e._slotsProxy||Xr(e._slotsProxy={},e.$scopedSlots),e._slotsProxy}function Xr(e,r){for(var i in r)e[i]=r[i];for(var i in e)i in r||delete e[i]}function so(){return gn().slots}function sc(){return gn().attrs}function fc(){return gn().listeners}function gn(){var e=K;return e._setupContext||(e._setupContext=Zr(e))}function cc(e,r){var i=g(e)?e.reduce(function(s,f){return s[f]={},s},{}):e;for(var a in r){var o=i[a];o?g(o)||y(o)?i[a]={type:o,default:r[a]}:o.default=r[a]:o===null&&(i[a]={default:r[a]})}return i}function fo(e){e._vnode=null,e._staticTrees=null;var r=e.$options,i=e.$vnode=r._parentVnode,a=i&&i.context;e.$slots=hn(r._renderChildren,a),e.$scopedSlots=i?gt(e.$parent,i.data.scopedSlots,e.$slots):B,e._c=function(s,f,c,u){return _t(e,s,f,c,u,!1)},e.$createElement=function(s,f,c,u){return _t(e,s,f,c,u,!0)};var o=i&&i.data;Ie(e,"$attrs",o&&o.attrs||B,null,!0),Ie(e,"$listeners",r._parentListeners||B,null,!0)}var Ft=null;function co(e){Yr(e.prototype),e.prototype.$nextTick=function(r){return Wt(r,this)},e.prototype._render=function(){var r=this,i=r.$options,a=i.render,o=i._parentVnode;o&&r._isMounted&&(r.$scopedSlots=gt(r.$parent,o.data.scopedSlots,r.$slots,r.$scopedSlots),r._slotsProxy&&Xr(r._slotsProxy,r.$scopedSlots)),r.$vnode=o;var s=K,f=Ft,c;try{Pe(r),Ft=r,c=a.call(r._renderProxy,r.$createElement)}catch(u){We(u,r,"render"),c=r._vnode}finally{Ft=f,Pe(s)}return g(c)&&c.length===1&&(c=c[0]),c instanceof fe||(c=ze()),c.parent=o,c}}function _n(e,r){return(e.__esModule||dt&&e[Symbol.toStringTag]==="Module")&&(e=e.default),$(e)?r.extend(e):e}function lo(e,r,i,a,o){var s=ze();return s.asyncFactory=e,s.asyncMeta={data:r,context:i,children:a,tag:o},s}function uo(e,r){if(x(e.error)&&l(e.errorComp))return e.errorComp;if(l(e.resolved))return e.resolved;var i=Ft;if(i&&l(e.owners)&&e.owners.indexOf(i)===-1&&e.owners.push(i),x(e.loading)&&l(e.loadingComp))return e.loadingComp;if(i&&!l(e.owners)){var a=e.owners=[i],o=!0,s=null,f=null;i.$on("hook:destroyed",function(){return Oe(a,i)});var c=function(b){for(var O=0,k=a.length;O<k;O++)a[O].$forceUpdate();b&&(a.length=0,s!==null&&(clearTimeout(s),s=null),f!==null&&(clearTimeout(f),f=null))},u=St(function(b){e.resolved=_n(b,r),o?a.length=0:c(!0)}),A=St(function(b){l(e.errorComp)&&(e.error=!0,c(!0))}),_=e(u,A);return $(_)&&(ke(_)?v(e.resolved)&&_.then(u,A):ke(_.component)&&(_.component.then(u,A),l(_.error)&&(e.errorComp=_n(_.error,r)),l(_.loading)&&(e.loadingComp=_n(_.loading,r),_.delay===0?e.loading=!0:s=setTimeout(function(){s=null,v(e.resolved)&&v(e.error)&&(e.loading=!0,c(!1))},_.delay||200)),l(_.timeout)&&(f=setTimeout(function(){f=null,v(e.resolved)&&A(null)},_.timeout)))),o=!1,e.loading?e.loadingComp:e.resolved}}function Kr(e){if(g(e))for(var r=0;r<e.length;r++){var i=e[r];if(l(i)&&(l(i.componentOptions)||ht(i)))return i}}var po=1,qr=2;function _t(e,r,i,a,o,s){return(g(i)||I(i))&&(o=a,a=i,i=void 0),x(s)&&(o=qr),Ao(e,r,i,a,o)}function Ao(e,r,i,a,o){if(l(i)&&l(i.__ob__)||(l(i)&&l(i.is)&&(r=i.is),!r))return ze();g(a)&&y(a[0])&&(i=i||{},i.scopedSlots={default:a[0]},a.length=0),o===qr?a=vn(a):o===po&&(a=Ha(a));var s,f;if(typeof r=="string"){var c=void 0;f=e.$vnode&&e.$vnode.ns||re.getTagNamespace(r),re.isReservedTag(r)?s=new fe(re.parsePlatformTagName(r),i,a,void 0,void 0,e):(!i||!i.pre)&&l(c=Zt(e.$options,"components",r))?s=ui(c,i,e,a,r):s=new fe(r,i,a,void 0,void 0,e)}else s=ui(r,i,e,a);return g(s)?s:l(s)?(l(f)&&Jr(s,f),l(i)&&vo(i),s):ze()}function Jr(e,r,i){if(e.ns=r,e.tag==="foreignObject"&&(r=void 0,i=!0),l(e.children))for(var a=0,o=e.children.length;a<o;a++){var s=e.children[a];l(s.tag)&&(v(s.ns)||x(i)&&s.tag!=="svg")&&Jr(s,r,i)}}function vo(e){$(e.style)&&at(e.style),$(e.class)&&at(e.class)}function ho(e,r,i){return _t(K,e,r,i,2,!0)}function We(e,r,i){nt();try{if(r)for(var a=r;a=a.$parent;){var o=a.$options.errorCaptured;if(o)for(var s=0;s<o.length;s++)try{var f=o[s].call(a,e,r,i)===!1;if(f)return}catch(c){Qr(c,a,"errorCaptured hook")}}Qr(e,r,i)}finally{rt()}}function xe(e,r,i,a,o){var s;try{s=i?e.apply(r,i):e.call(r),s&&!s._isVue&&ke(s)&&!s._handled&&(s.catch(function(f){return We(f,a,o+" (Promise/async)")}),s._handled=!0)}catch(f){We(f,a,o)}return s}function Qr(e,r,i){if(re.errorHandler)try{return re.errorHandler.call(null,e,r,i)}catch(a){a!==e&&Vr(a,null,"config.errorHandler")}Vr(e,r,i)}function Vr(e,r,i){if(se&&typeof console<"u")console.error(e);else throw e}var mn=!1,Cn=[],bn=!1;function zt(){bn=!1;var e=Cn.slice(0);Cn.length=0;for(var r=0;r<e.length;r++)e[r]()}var mt;if(typeof Promise<"u"&&et(Promise)){var go=Promise.resolve();mt=function(){go.then(zt),Ca&&setTimeout(X)},mn=!0}else if(!Qe&&typeof MutationObserver<"u"&&(et(MutationObserver)||MutationObserver.toString()==="[object MutationObserverConstructor]")){var Ut=1,_o=new MutationObserver(zt),ei=document.createTextNode(String(Ut));_o.observe(ei,{characterData:!0}),mt=function(){Ut=(Ut+1)%2,ei.data=String(Ut)},mn=!0}else typeof setImmediate<"u"&&et(setImmediate)?mt=function(){setImmediate(zt)}:mt=function(){setTimeout(zt,0)};function Wt(e,r){var i;if(Cn.push(function(){if(e)try{e.call(r)}catch(a){We(a,r,"nextTick")}else i&&i(r)}),bn||(bn=!0,mt()),!e&&typeof Promise<"u")return new Promise(function(a){i=a})}function lc(e){e===void 0&&(e="$style");{if(!K)return B;var r=K[e];return r||B}}function mo(e){if(se){var r=K;r&&Ra(function(){var i=r.$el,a=e(r,r._setupProxy);if(i&&i.nodeType===1){var o=i.style;for(var s in a)o.setProperty("--".concat(s),a[s])}})}}function Co(e){y(e)&&(e={loader:e});var r=e.loader,i=e.loadingComponent,a=e.errorComponent,o=e.delay,s=o===void 0?200:o,f=e.timeout,c=e.suspensible,u=c===void 0?!1:c,A=e.onError,_=null,b=0,O=function(){return b++,_=null,k()},k=function(){var z;return _||(z=_=r().catch(function(M){if(M=M instanceof Error?M:new Error(String(M)),A)return new Promise(function(ce,V){var Q=function(){return ce(O())},U=function(){return V(M)};A(M,Q,U,b+1)});throw M}).then(function(M){return z!==_&&_?_:(M&&(M.__esModule||M[Symbol.toStringTag]==="Module")&&(M=M.default),M)}))};return function(){var z=k();return{component:z,delay:s,timeout:f,error:a,loading:i}}}function pe(e){return function(r,i){if(i===void 0&&(i=K),!!i)return bo(i,e,r)}}function uc(e){return e==="beforeDestroy"?e="beforeUnmount":e==="destroyed"&&(e="unmounted"),"on".concat(e[0].toUpperCase()+e.slice(1))}function bo(e,r,i){var a=e.$options;a[r]=pi(a[r],i)}var dc=pe("beforeMount"),yo=pe("mounted"),pc=pe("beforeUpdate"),Ac=pe("updated"),vc=pe("beforeDestroy"),xo=pe("destroyed"),hc=pe("activated"),gc=pe("deactivated"),_c=pe("serverPrefetch"),mc=pe("renderTracked"),Cc=pe("renderTriggered"),wo=pe("errorCaptured");function bc(e,r){r===void 0&&(r=K),wo(e,r)}var Eo="2.7.16";function Bo(e){return e}var ti=new pt;function at(e){return Ht(e,ti),ti.clear(),e}function Ht(e,r){var i,a,o=g(e);if(!(!o&&!$(e)||e.__v_skip||Object.isFrozen(e)||e instanceof fe)){if(e.__ob__){var s=e.__ob__.dep.id;if(r.has(s))return;r.add(s)}if(o)for(i=e.length;i--;)Ht(e[i],r);else if(te(e))Ht(e.value,r);else for(a=Object.keys(e),i=a.length;i--;)Ht(e[a[i]],r)}}var ko=0,Ct=function(){function e(r,i,a,o,s){za(this,ae&&!ae._vm?ae:r?r._scope:void 0),(this.vm=r)&&s&&(r._watcher=this),o?(this.deep=!!o.deep,this.user=!!o.user,this.lazy=!!o.lazy,this.sync=!!o.sync,this.before=o.before):this.deep=this.user=this.lazy=this.sync=!1,this.cb=a,this.id=++ko,this.active=!0,this.post=!1,this.dirty=this.lazy,this.deps=[],this.newDeps=[],this.depIds=new pt,this.newDepIds=new pt,this.expression="",y(i)?this.getter=i:(this.getter=_a(i),this.getter||(this.getter=X)),this.value=this.lazy?void 0:this.get()}return e.prototype.get=function(){nt(this);var r,i=this.vm;try{r=this.getter.call(i,i)}catch(a){if(this.user)We(a,i,'getter for watcher "'.concat(this.expression,'"'));else throw a}finally{this.deep&&at(r),rt(),this.cleanupDeps()}return r},e.prototype.addDep=function(r){var i=r.id;this.newDepIds.has(i)||(this.newDepIds.add(i),this.newDeps.push(r),this.depIds.has(i)||r.addSub(this))},e.prototype.cleanupDeps=function(){for(var r=this.deps.length;r--;){var i=this.deps[r];this.newDepIds.has(i.id)||i.removeSub(this)}var a=this.depIds;this.depIds=this.newDepIds,this.newDepIds=a,this.newDepIds.clear(),a=this.deps,this.deps=this.newDeps,this.newDeps=a,this.newDeps.length=0},e.prototype.update=function(){this.lazy?this.dirty=!0:this.sync?this.run():Sn(this)},e.prototype.run=function(){if(this.active){var r=this.get();if(r!==this.value||$(r)||this.deep){var i=this.value;if(this.value=r,this.user){var a='callback for watcher "'.concat(this.expression,'"');xe(this.cb,this.vm,[r,i],this.vm,a)}else this.cb.call(this.vm,r,i)}}},e.prototype.evaluate=function(){this.value=this.get(),this.dirty=!1},e.prototype.depend=function(){for(var r=this.deps.length;r--;)this.deps[r].depend()},e.prototype.teardown=function(){if(this.vm&&!this.vm._isBeingDestroyed&&Oe(this.vm._scope.effects,this),this.active){for(var r=this.deps.length;r--;)this.deps[r].removeSub(this);this.active=!1,this.onStop&&this.onStop()}},e}(),yc,xc;if(0)var wc;function So(e){e._events=Object.create(null),e._hasHookEvent=!1;var r=e.$options._parentListeners;r&&ni(e,r)}var bt;function Oo(e,r){bt.$on(e,r)}function To(e,r){bt.$off(e,r)}function Po(e,r){var i=bt;return function a(){var o=r.apply(null,arguments);o!==null&&i.$off(e,a)}}function ni(e,r,i){bt=e,Rr(r,i||{},Oo,To,Po,e),bt=void 0}function $o(e){var r=/^hook:/;e.prototype.$on=function(i,a){var o=this;if(g(i))for(var s=0,f=i.length;s<f;s++)o.$on(i[s],a);else(o._events[i]||(o._events[i]=[])).push(a),r.test(i)&&(o._hasHookEvent=!0);return o},e.prototype.$once=function(i,a){var o=this;function s(){o.$off(i,s),a.apply(o,arguments)}return s.fn=a,o.$on(i,s),o},e.prototype.$off=function(i,a){var o=this;if(!arguments.length)return o._events=Object.create(null),o;if(g(i)){for(var s=0,f=i.length;s<f;s++)o.$off(i[s],a);return o}var c=o._events[i];if(!c)return o;if(!a)return o._events[i]=null,o;for(var u,A=c.length;A--;)if(u=c[A],u===a||u.fn===a){c.splice(A,1);break}return o},e.prototype.$emit=function(i){var a=this;if(0)var o;var s=a._events[i];if(s){s=s.length>1?sn(s):s;for(var f=sn(arguments,1),c='event handler for "'.concat(i,'"'),u=0,A=s.length;u<A;u++)xe(s[u],a,f,a,c)}return a}}var He=null,Ec=!1;function ri(e){var r=He;return He=e,function(){He=r}}function Io(e){var r=e.$options,i=r.parent;if(i&&!r.abstract){for(;i.$options.abstract&&i.$parent;)i=i.$parent;i.$children.push(e)}e.$parent=i,e.$root=i?i.$root:e,e.$children=[],e.$refs={},e._provided=i?i._provided:Object.create(null),e._watcher=null,e._inactive=null,e._directInactive=!1,e._isMounted=!1,e._isDestroyed=!1,e._isBeingDestroyed=!1}function Do(e){e.prototype._update=function(r,i){var a=this,o=a.$el,s=a._vnode,f=ri(a);a._vnode=r,s?a.$el=a.__patch__(s,r):a.$el=a.__patch__(a.$el,r,i,!1),f(),o&&(o.__vue__=null),a.$el&&(a.$el.__vue__=a);for(var c=a;c&&c.$vnode&&c.$parent&&c.$vnode===c.$parent._vnode;)c.$parent.$el=c.$el,c=c.$parent},e.prototype.$forceUpdate=function(){var r=this;r._watcher&&r._watcher.update()},e.prototype.$destroy=function(){var r=this;if(!r._isBeingDestroyed){_e(r,"beforeDestroy"),r._isBeingDestroyed=!0;var i=r.$parent;i&&!i._isBeingDestroyed&&!r.$options.abstract&&Oe(i.$children,r),r._scope.stop(),r._data.__ob__&&r._data.__ob__.vmCount--,r._isDestroyed=!0,r.__patch__(r._vnode,null),_e(r,"destroyed"),r.$off(),r.$el&&(r.$el.__vue__=null),r.$vnode&&(r.$vnode.parent=null)}}}function No(e,r,i){e.$el=r,e.$options.render||(e.$options.render=ze),_e(e,"beforeMount");var a;a=function(){e._update(e._render(),i)};var o={before:function(){e._isMounted&&!e._isDestroyed&&_e(e,"beforeUpdate")}};new Ct(e,a,X,o,!0),i=!1;var s=e._preWatchers;if(s)for(var f=0;f<s.length;f++)s[f].run();return e.$vnode==null&&(e._isMounted=!0,_e(e,"mounted")),e}function Mo(e,r,i,a,o){var s=a.data.scopedSlots,f=e.$scopedSlots,c=!!(s&&!s.$stable||f!==B&&!f.$stable||s&&e.$scopedSlots.$key!==s.$key||!s&&e.$scopedSlots.$key),u=!!(o||e.$options._renderChildren||c),A=e.$vnode;e.$options._parentVnode=a,e.$vnode=a,e._vnode&&(e._vnode.parent=a),e.$options._renderChildren=o;var _=a.data.attrs||B;e._attrsProxy&&Rt(e._attrsProxy,_,A.data&&A.data.attrs||B,e,"$attrs")&&(u=!0),e.$attrs=_,i=i||B;var b=e.$options._parentListeners;if(e._listenersProxy&&Rt(e._listenersProxy,i,b||B,e,"$listeners"),e.$listeners=e.$options._parentListeners=i,ni(e,i,b),r&&e.$options.props){$e(!1);for(var O=e._props,k=e.$options._propKeys||[],z=0;z<k.length;z++){var M=k[z],ce=e.$options.props;O[M]=Pn(M,ce,r,e)}$e(!0),e.$options.propsData=r}u&&(e.$slots=hn(o,a.context),e.$forceUpdate())}function ii(e){for(;e&&(e=e.$parent);)if(e._inactive)return!0;return!1}function yn(e,r){if(r){if(e._directInactive=!1,ii(e))return}else if(e._directInactive)return;if(e._inactive||e._inactive===null){e._inactive=!1;for(var i=0;i<e.$children.length;i++)yn(e.$children[i]);_e(e,"activated")}}function ai(e,r){if(!(r&&(e._directInactive=!0,ii(e)))&&!e._inactive){e._inactive=!0;for(var i=0;i<e.$children.length;i++)ai(e.$children[i]);_e(e,"deactivated")}}function _e(e,r,i,a){a===void 0&&(a=!0),nt();var o=K,s=Mr();a&&Pe(e);var f=e.$options[r],c="".concat(r," hook");if(f)for(var u=0,A=f.length;u<A;u++)xe(f[u],e,i||null,e,c);e._hasHookEvent&&e.$emit("hook:"+r),a&&(Pe(o),s&&s.on()),rt()}var Bc=100,we=[],xn=[],Gt={},kc={},wn=!1,En=!1,ot=0;function jo(){ot=we.length=xn.length=0,Gt={},wn=En=!1}var oi=0,Bn=Date.now;if(se&&!Qe){var kn=window.performance;kn&&typeof kn.now=="function"&&Bn()>document.createEvent("Event").timeStamp&&(Bn=function(){return kn.now()})}var Lo=function(e,r){if(e.post){if(!r.post)return 1}else if(r.post)return-1;return e.id-r.id};function Ro(){oi=Bn(),En=!0;var e,r;for(we.sort(Lo),ot=0;ot<we.length;ot++)e=we[ot],e.before&&e.before(),r=e.id,Gt[r]=null,e.run();var i=xn.slice(),a=we.slice();jo(),Uo(i),Fo(a),xa(),Pt&&re.devtools&&Pt.emit("flush")}function Fo(e){for(var r=e.length;r--;){var i=e[r],a=i.vm;a&&a._watcher===i&&a._isMounted&&!a._isDestroyed&&_e(a,"updated")}}function zo(e){e._inactive=!1,xn.push(e)}function Uo(e){for(var r=0;r<e.length;r++)e[r]._inactive=!0,yn(e[r],!0)}function Sn(e){var r=e.id;if(Gt[r]==null&&!(e===me.target&&e.noRecurse)){if(Gt[r]=!0,!En)we.push(e);else{for(var i=we.length-1;i>ot&&we[i].id>e.id;)i--;we.splice(i+1,0,e)}wn||(wn=!0,Wt(Ro))}}function Wo(e){var r=e.$options.provide;if(r){var i=y(r)?r.call(e):r;if(!$(i))return;for(var a=jr(e),o=dt?Reflect.ownKeys(i):Object.keys(i),s=0;s<o.length;s++){var f=o[s];Object.defineProperty(a,f,Object.getOwnPropertyDescriptor(i,f))}}}function Ho(e){var r=si(e.$options.inject,e);r&&($e(!1),Object.keys(r).forEach(function(i){Ie(e,i,r[i])}),$e(!0))}function si(e,r){if(e){for(var i=Object.create(null),a=dt?Reflect.ownKeys(e):Object.keys(e),o=0;o<a.length;o++){var s=a[o];if(s!=="__ob__"){var f=e[s].from;if(f in r._provided)i[s]=r._provided[f];else if("default"in e[s]){var c=e[s].default;i[s]=y(c)?c.call(r):c}}}return i}}function On(e,r,i,a,o){var s=this,f=o.options,c;oe(a,"_uid")?(c=Object.create(a),c._original=a):(c=a,a=a._original);var u=x(f._compiled),A=!u;this.data=e,this.props=r,this.children=i,this.parent=a,this.listeners=e.on||B,this.injections=si(f.inject,a),this.slots=function(){return s.$slots||gt(a,e.scopedSlots,s.$slots=hn(i,a)),s.$slots},Object.defineProperty(this,"scopedSlots",{enumerable:!0,get:function(){return gt(a,e.scopedSlots,this.slots())}}),u&&(this.$options=f,this.$slots=this.slots(),this.$scopedSlots=gt(a,e.scopedSlots,this.$slots)),f._scopeId?this._c=function(_,b,O,k){var z=_t(c,_,b,O,k,A);return z&&!g(z)&&(z.fnScopeId=f._scopeId,z.fnContext=a),z}:this._c=function(_,b,O,k){return _t(c,_,b,O,k,A)}}Yr(On.prototype);function Go(e,r,i,a,o){var s=e.options,f={},c=s.props;if(l(c))for(var u in c)f[u]=Pn(u,c,r||B);else l(i.attrs)&&ci(f,i.attrs),l(i.props)&&ci(f,i.props);var A=new On(i,f,o,a,e),_=s.render.call(null,A._c,A);if(_ instanceof fe)return fi(_,i,A.parent,s,A);if(g(_)){for(var b=vn(_)||[],O=new Array(b.length),k=0;k<b.length;k++)O[k]=fi(b[k],i,A.parent,s,A);return O}}function fi(e,r,i,a,o){var s=un(e);return s.fnContext=i,s.fnOptions=a,r.slot&&((s.data||(s.data={})).slot=r.slot),s}function ci(e,r){for(var i in r)e[Re(i)]=r[i]}function Yt(e){return e.name||e.__name||e._componentTag}var Tn={init:function(e,r){if(e.componentInstance&&!e.componentInstance._isDestroyed&&e.data.keepAlive){var i=e;Tn.prepatch(i,i)}else{var a=e.componentInstance=Yo(e,He);a.$mount(r?e.elm:void 0,r)}},prepatch:function(e,r){var i=r.componentOptions,a=r.componentInstance=e.componentInstance;Mo(a,i.propsData,i.listeners,r,i.children)},insert:function(e){var r=e.context,i=e.componentInstance;i._isMounted||(i._isMounted=!0,_e(i,"mounted")),e.data.keepAlive&&(r._isMounted?zo(i):yn(i,!0))},destroy:function(e){var r=e.componentInstance;r._isDestroyed||(e.data.keepAlive?ai(r,!0):r.$destroy())}},li=Object.keys(Tn);function ui(e,r,i,a,o){if(!v(e)){var s=i.$options._base;if($(e)&&(e=s.extend(e)),typeof e=="function"){var f;if(v(e.cid)&&(f=e,e=uo(f,s),e===void 0))return lo(f,r,i,a,o);r=r||{},Nn(e),l(r.model)&&Ko(e.options,r);var c=Wa(r,e,o);if(x(e.options.functional))return Go(e,c,r,i,a);var u=r.on;if(r.on=r.nativeOn,x(e.options.abstract)){var A=r.slot;r={},A&&(r.slot=A)}Zo(r);var _=Yt(e.options)||o,b=new fe("vue-component-".concat(e.cid).concat(_?"-".concat(_):""),r,void 0,void 0,void 0,i,{Ctor:e,propsData:c,listeners:u,tag:o,children:a},f);return b}}}function Yo(e,r){var i={_isComponent:!0,_parentVnode:e,parent:r},a=e.data.inlineTemplate;return l(a)&&(i.render=a.render,i.staticRenderFns=a.staticRenderFns),new e.componentOptions.Ctor(i)}function Zo(e){for(var r=e.hook||(e.hook={}),i=0;i<li.length;i++){var a=li[i],o=r[a],s=Tn[a];o!==s&&!(o&&o._merged)&&(r[a]=o?Xo(s,o):s)}}function Xo(e,r){var i=function(a,o){e(a,o),r(a,o)};return i._merged=!0,i}function Ko(e,r){var i=e.model&&e.model.prop||"value",a=e.model&&e.model.event||"input";(r.attrs||(r.attrs={}))[i]=r.model.value;var o=r.on||(r.on={}),s=o[a],f=r.model.callback;l(s)?(g(s)?s.indexOf(f)===-1:s!==f)&&(o[a]=[f].concat(s)):o[a]=f}var Ae=X,Sc=null,Oc,Tc;if(0)var Pc,$c,Ic,Dc;var Ce=re.optionMergeStrategies;function yt(e,r,i){if(i===void 0&&(i=!0),!r)return e;for(var a,o,s,f=dt?Reflect.ownKeys(r):Object.keys(r),c=0;c<f.length;c++)a=f[c],a!=="__ob__"&&(o=e[a],s=r[a],!i||!oe(e,a)?Nt(e,a,s):o!==s&&D(o)&&D(s)&&yt(o,s));return e}function di(e,r,i){return i?function(){var o=y(r)?r.call(i,i):r,s=y(e)?e.call(i,i):e;return o?yt(o,s):s}:r?e?function(){return yt(y(r)?r.call(this,this):r,y(e)?e.call(this,this):e)}:r:e}Ce.data=function(e,r,i){return i?di(e,r,i):r&&typeof r!="function"?e:di(e,r)};function pi(e,r){var i=r?e?e.concat(r):g(r)?r:[r]:e;return i&&qo(i)}function qo(e){for(var r=[],i=0;i<e.length;i++)r.indexOf(e[i])===-1&&r.push(e[i]);return r}pr.forEach(function(e){Ce[e]=pi});function Jo(e,r,i,a){var o=Object.create(e||null);return r?Y(o,r):o}Ot.forEach(function(e){Ce[e+"s"]=Jo}),Ce.watch=function(e,r,i,a){if(e===ln&&(e=void 0),r===ln&&(r=void 0),!r)return Object.create(e||null);if(!e)return r;var o={};Y(o,e);for(var s in r){var f=o[s],c=r[s];f&&!g(f)&&(f=[f]),o[s]=f?f.concat(c):g(c)?c:[c]}return o},Ce.props=Ce.methods=Ce.inject=Ce.computed=function(e,r,i,a){if(!e)return r;var o=Object.create(null);return Y(o,e),r&&Y(o,r),o},Ce.provide=function(e,r){return e?function(){var i=Object.create(null);return yt(i,y(e)?e.call(this):e),r&&yt(i,y(r)?r.call(this):r,!1),i}:r};var Qo=function(e,r){return r===void 0?e:r};function Nc(e){for(var r in e.components)Vo(r)}function Vo(e){new RegExp("^[a-zA-Z][\\-\\.0-9_".concat(Ar.source,"]*$")).test(e)||Ae('Invalid component name: "'+e+'". Component names should conform to valid custom element name in html5 specification.'),(on(e)||re.isReservedTag(e))&&Ae("Do not use built-in or reserved HTML elements as component id: "+e)}function es(e,r){var i=e.props;if(i){var a={},o,s,f;if(g(i))for(o=i.length;o--;)s=i[o],typeof s=="string"&&(f=Re(s),a[f]={type:null});else if(D(i))for(var c in i)s=i[c],f=Re(c),a[f]=D(s)?s:{type:s};e.props=a}}function ts(e,r){var i=e.inject;if(i){var a=e.inject={};if(g(i))for(var o=0;o<i.length;o++)a[i[o]]={from:i[o]};else if(D(i))for(var s in i){var f=i[s];a[s]=D(f)?Y({from:s},f):{from:f}}}}function ns(e){var r=e.directives;if(r)for(var i in r){var a=r[i];y(a)&&(r[i]={bind:a,update:a})}}function Mc(e,r,i){D(r)||Ae('Invalid value for option "'.concat(e,'": expected an Object, ')+"but got ".concat(P(r),"."),i)}function Ge(e,r,i){if(y(r)&&(r=r.options),es(r,i),ts(r,i),ns(r),!r._base&&(r.extends&&(e=Ge(e,r.extends,i)),r.mixins))for(var a=0,o=r.mixins.length;a<o;a++)e=Ge(e,r.mixins[a],i);var s={},f;for(f in e)c(f);for(f in r)oe(e,f)||c(f);function c(u){var A=Ce[u]||Qo;s[u]=A(e[u],r[u],i,u)}return s}function Zt(e,r,i,a){if(typeof i=="string"){var o=e[r];if(oe(o,i))return o[i];var s=Re(i);if(oe(o,s))return o[s];var f=sr(s);if(oe(o,f))return o[f];var c=o[i]||o[s]||o[f];return c}}function Pn(e,r,i,a){var o=r[e],s=!oe(i,e),f=i[e],c=vi(Boolean,o.type);if(c>-1){if(s&&!oe(o,"default"))f=!1;else if(f===""||f===ut(e)){var u=vi(String,o.type);(u<0||c<u)&&(f=!0)}}if(f===void 0){f=rs(a,o,e);var A=dn;$e(!0),ye(f),$e(A)}return f}function rs(e,r,i){if(oe(r,"default")){var a=r.default;return e&&e.$options.propsData&&e.$options.propsData[i]===void 0&&e._props[i]!==void 0?e._props[i]:y(a)&&Xt(r.type)!=="Function"?a.call(e):a}}function jc(e,r,i,a,o){if(e.required&&o){Ae('Missing required prop: "'+r+'"',a);return}if(!(i==null&&!e.required)){var s=e.type,f=!s||s===!0,c=[];if(s){g(s)||(s=[s]);for(var u=0;u<s.length&&!f;u++){var A=as(i,s[u],a);c.push(A.expectedType||""),f=A.valid}}var _=c.some(function(O){return O});if(!f&&_){Ae(ss(r,i,c),a);return}var b=e.validator;b&&(b(i)||Ae('Invalid prop: custom validator check failed for prop "'+r+'".',a))}}var is=/^(String|Number|Boolean|Function|Symbol|BigInt)$/;function as(e,r,i){var a,o=Xt(r);if(is.test(o)){var s=typeof e;a=s===o.toLowerCase(),!a&&s==="object"&&(a=e instanceof r)}else if(o==="Object")a=D(e);else if(o==="Array")a=g(e);else try{a=e instanceof r}catch{Ae('Invalid prop type: "'+String(r)+'" is not a constructor',i),a=!1}return{valid:a,expectedType:o}}var os=/^\s*function (\w+)/;function Xt(e){var r=e&&e.toString().match(os);return r?r[1]:""}function Ai(e,r){return Xt(e)===Xt(r)}function vi(e,r){if(!g(r))return Ai(r,e)?0:-1;for(var i=0,a=r.length;i<a;i++)if(Ai(r[i],e))return i;return-1}function ss(e,r,i){var a='Invalid prop: type check failed for prop "'.concat(e,'".')+" Expected ".concat(i.map(sr).join(", ")),o=i[0],s=P(r);return i.length===1&&$n(o)&&$n(typeof r)&&!cs(o,s)&&(a+=" with value ".concat(hi(r,o))),a+=", got ".concat(s," "),$n(s)&&(a+="with value ".concat(hi(r,s),".")),a}function hi(e,r){return r==="String"?'"'.concat(e,'"'):r==="Number"?"".concat(Number(e)):"".concat(e)}var fs=null;function $n(e){return fs.some(function(r){return e.toLowerCase()===r})}function cs(){for(var e=[],r=0;r<arguments.length;r++)e[r]=arguments[r];return e.some(function(i){return i.toLowerCase()==="boolean"})}var Lc;if(0)var Rc,Fc,zc,Uc,Wc,Hc,Gc;var Ne={enumerable:!0,configurable:!0,get:X,set:X};function In(e,r,i){Ne.get=function(){return this[r][i]},Ne.set=function(o){this[r][i]=o},Object.defineProperty(e,i,Ne)}function ls(e){var r=e.$options;if(r.props&&us(e,r.props),io(e),r.methods&&hs(e,r.methods),r.data)ds(e);else{var i=ye(e._data={});i&&i.vmCount++}r.computed&&vs(e,r.computed),r.watch&&r.watch!==ln&&gs(e,r.watch)}function us(e,r){var i=e.$options.propsData||{},a=e._props=Br({}),o=e.$options._propKeys=[],s=!e.$parent;s||$e(!1);var f=function(u){o.push(u);var A=Pn(u,r,i,e);if(0)var _;else Ie(a,u,A,void 0,!0);u in e||In(e,"_props",u)};for(var c in r)f(c);$e(!0)}function ds(e){var r=e.$options.data;r=e._data=y(r)?ps(r,e):r||{},D(r)||(r={});for(var i=Object.keys(r),a=e.$options.props,o=e.$options.methods,s=i.length;s--;){var f=i[s];a&&oe(a,f)||vr(f)||In(e,"_data",f)}var c=ye(r);c&&c.vmCount++}function ps(e,r){nt();try{return e.call(r,r)}catch(i){return We(i,r,"data()"),{}}finally{rt()}}var As={lazy:!0};function vs(e,r){var i=e._computedWatchers=Object.create(null),a=Te();for(var o in r){var s=r[o],f=y(s)?s:s.get;a||(i[o]=new Ct(e,f||X,X,As)),o in e||gi(e,o,s)}}function gi(e,r,i){var a=!Te();y(i)?(Ne.get=a?_i(r):mi(i),Ne.set=X):(Ne.get=i.get?a&&i.cache!==!1?_i(r):mi(i.get):X,Ne.set=i.set||X),Object.defineProperty(e,r,Ne)}function _i(e){return function(){var i=this._computedWatchers&&this._computedWatchers[e];if(i)return i.dirty&&i.evaluate(),me.target&&i.depend(),i.value}}function mi(e){return function(){return e.call(this,this)}}function hs(e,r){var i=e.$options.props;for(var a in r)e[a]=typeof r[a]!="function"?X:fr(r[a],e)}function gs(e,r){for(var i in r){var a=r[i];if(g(a))for(var o=0;o<a.length;o++)Dn(e,i,a[o]);else Dn(e,i,a)}}function Dn(e,r,i,a){return D(i)&&(a=i,i=i.handler),typeof i=="string"&&(i=e[i]),e.$watch(r,i,a)}function _s(e){var r={};r.get=function(){return this._data};var i={};i.get=function(){return this._props},Object.defineProperty(e.prototype,"$data",r),Object.defineProperty(e.prototype,"$props",i),e.prototype.$set=Nt,e.prototype.$delete=wr,e.prototype.$watch=function(a,o,s){var f=this;if(D(o))return Dn(f,a,o,s);s=s||{},s.user=!0;var c=new Ct(f,a,o,s);if(s.immediate){var u='callback for immediate watcher "'.concat(c.expression,'"');nt(),xe(o,f,[c.value],f,u),rt()}return function(){c.teardown()}}}var ms=0;function Cs(e){e.prototype._init=function(r){var i=this;i._uid=ms++;var a,o;i._isVue=!0,i.__v_skip=!0,i._scope=new Nr(!0),i._scope.parent=void 0,i._scope._vm=!0,r&&r._isComponent?bs(i,r):i.$options=Ge(Nn(i.constructor),r||{},i),i._renderProxy=i,i._self=i,Io(i),So(i),fo(i),_e(i,"beforeCreate",void 0,!1),Ho(i),ls(i),Wo(i),_e(i,"created"),i.$options.el&&i.$mount(i.$options.el)}}function bs(e,r){var i=e.$options=Object.create(e.constructor.options),a=r._parentVnode;i.parent=r.parent,i._parentVnode=a;var o=a.componentOptions;i.propsData=o.propsData,i._parentListeners=o.listeners,i._renderChildren=o.children,i._componentTag=o.tag,r.render&&(i.render=r.render,i.staticRenderFns=r.staticRenderFns)}function Nn(e){var r=e.options;if(e.super){var i=Nn(e.super),a=e.superOptions;if(i!==a){e.superOptions=i;var o=ys(e);o&&Y(e.extendOptions,o),r=e.options=Ge(i,e.extendOptions),r.name&&(r.components[r.name]=e)}}return r}function ys(e){var r,i=e.options,a=e.sealedOptions;for(var o in i)i[o]!==a[o]&&(r||(r={}),r[o]=i[o]);return r}function J(e){this._init(e)}Cs(J),_s(J),$o(J),Do(J),co(J);function xs(e){e.use=function(r){var i=this._installedPlugins||(this._installedPlugins=[]);if(i.indexOf(r)>-1)return this;var a=sn(arguments,1);return a.unshift(this),y(r.install)?r.install.apply(r,a):y(r)&&r.apply(null,a),i.push(r),this}}function ws(e){e.mixin=function(r){return this.options=Ge(this.options,r),this}}function Es(e){e.cid=0;var r=1;e.extend=function(i){i=i||{};var a=this,o=a.cid,s=i._Ctor||(i._Ctor={});if(s[o])return s[o];var f=Yt(i)||Yt(a.options),c=function(A){this._init(A)};return c.prototype=Object.create(a.prototype),c.prototype.constructor=c,c.cid=r++,c.options=Ge(a.options,i),c.super=a,c.options.props&&Bs(c),c.options.computed&&ks(c),c.extend=a.extend,c.mixin=a.mixin,c.use=a.use,Ot.forEach(function(u){c[u]=a[u]}),f&&(c.options.components[f]=c),c.superOptions=a.options,c.extendOptions=i,c.sealedOptions=Y({},c.options),s[o]=c,c}}function Bs(e){var r=e.options.props;for(var i in r)In(e.prototype,"_props",i)}function ks(e){var r=e.options.computed;for(var i in r)gi(e.prototype,i,r[i])}function Ss(e){Ot.forEach(function(r){e[r]=function(i,a){return a?(r==="component"&&D(a)&&(a.name=a.name||i,a=this.options._base.extend(a)),r==="directive"&&y(a)&&(a={bind:a,update:a}),this.options[r+"s"][i]=a,a):this.options[r+"s"][i]}})}function Ci(e){return e&&(Yt(e.Ctor.options)||e.tag)}function Kt(e,r){return g(e)?e.indexOf(r)>-1:typeof e=="string"?e.split(",").indexOf(r)>-1:he(e)?e.test(r):!1}function bi(e,r){var i=e.cache,a=e.keys,o=e._vnode,s=e.$vnode;for(var f in i){var c=i[f];if(c){var u=c.name;u&&!r(u)&&Mn(i,f,a,o)}}s.componentOptions.children=void 0}function Mn(e,r,i,a){var o=e[r];o&&(!a||o.tag!==a.tag)&&o.componentInstance.$destroy(),e[r]=null,Oe(i,r)}var yi=[String,RegExp,Array],Os={name:"keep-alive",abstract:!0,props:{include:yi,exclude:yi,max:[String,Number]},methods:{cacheVNode:function(){var e=this,r=e.cache,i=e.keys,a=e.vnodeToCache,o=e.keyToCache;if(a){var s=a.tag,f=a.componentInstance,c=a.componentOptions;r[o]={name:Ci(c),tag:s,componentInstance:f},i.push(o),this.max&&i.length>parseInt(this.max)&&Mn(r,i[0],i,this._vnode),this.vnodeToCache=null}}},created:function(){this.cache=Object.create(null),this.keys=[]},destroyed:function(){for(var e in this.cache)Mn(this.cache,e,this.keys)},mounted:function(){var e=this;this.cacheVNode(),this.$watch("include",function(r){bi(e,function(i){return Kt(r,i)})}),this.$watch("exclude",function(r){bi(e,function(i){return!Kt(r,i)})})},updated:function(){this.cacheVNode()},render:function(){var e=this.$slots.default,r=Kr(e),i=r&&r.componentOptions;if(i){var a=Ci(i),o=this,s=o.include,f=o.exclude;if(s&&(!a||!Kt(s,a))||f&&a&&Kt(f,a))return r;var c=this,u=c.cache,A=c.keys,_=r.key==null?i.Ctor.cid+(i.tag?"::".concat(i.tag):""):r.key;u[_]?(r.componentInstance=u[_].componentInstance,Oe(A,_),A.push(_)):(this.vnodeToCache=r,this.keyToCache=_),r.data.keepAlive=!0}return r||e&&e[0]}},Ts={KeepAlive:Os};function Ps(e){var r={};r.get=function(){return re},Object.defineProperty(e,"config",r),e.util={warn:Ae,extend:Y,mergeOptions:Ge,defineReactive:Ie},e.set=Nt,e.delete=wr,e.nextTick=Wt,e.observable=function(i){return ye(i),i},e.options=Object.create(null),Ot.forEach(function(i){e.options[i+"s"]=Object.create(null)}),e.options._base=e,Y(e.options.components,Ts),xs(e),ws(e),Es(e),Ss(e)}Ps(J),Object.defineProperty(J.prototype,"$isServer",{get:Te}),Object.defineProperty(J.prototype,"$ssrContext",{get:function(){return this.$vnode&&this.$vnode.ssrContext}}),Object.defineProperty(J,"FunctionalRenderContext",{value:On}),J.version=Eo;var $s=de("style,class"),Is=de("input,textarea,option,select,progress"),Ds=function(e,r,i){return i==="value"&&Is(e)&&r!=="button"||i==="selected"&&e==="option"||i==="checked"&&e==="input"||i==="muted"&&e==="video"},xi=de("contenteditable,draggable,spellcheck"),Ns=de("events,caret,typing,plaintext-only"),Ms=function(e,r){return qt(r)||r==="false"?"false":e==="contenteditable"&&Ns(r)?r:"true"},js=de("allowfullscreen,async,autofocus,autoplay,checked,compact,controls,declare,default,defaultchecked,defaultmuted,defaultselected,defer,disabled,enabled,formnovalidate,hidden,indeterminate,inert,ismap,itemscope,loop,multiple,muted,nohref,noresize,noshade,novalidate,nowrap,open,pauseonexit,readonly,required,reversed,scoped,seamless,selected,sortable,truespeed,typemustmatch,visible"),jn="http://www.w3.org/1999/xlink",Ln=function(e){return e.charAt(5)===":"&&e.slice(0,5)==="xlink"},wi=function(e){return Ln(e)?e.slice(6,e.length):""},qt=function(e){return e==null||e===!1};function Ls(e){for(var r=e.data,i=e,a=e;l(a.componentInstance);)a=a.componentInstance._vnode,a&&a.data&&(r=Ei(a.data,r));for(;l(i=i.parent);)i&&i.data&&(r=Ei(r,i.data));return Rs(r.staticClass,r.class)}function Ei(e,r){return{staticClass:Rn(e.staticClass,r.staticClass),class:l(e.class)?[e.class,r.class]:r.class}}function Rs(e,r){return l(e)||l(r)?Rn(e,Fn(r)):""}function Rn(e,r){return e?r?e+" "+r:e:r||""}function Fn(e){return Array.isArray(e)?Fs(e):$(e)?zs(e):typeof e=="string"?e:""}function Fs(e){for(var r="",i,a=0,o=e.length;a<o;a++)l(i=Fn(e[a]))&&i!==""&&(r&&(r+=" "),r+=i);return r}function zs(e){var r="";for(var i in e)e[i]&&(r&&(r+=" "),r+=i);return r}var Us={svg:"http://www.w3.org/2000/svg",math:"http://www.w3.org/1998/Math/MathML"},Ws=de("html,body,base,head,link,meta,style,title,address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,hgroup,nav,section,div,dd,dl,dt,figcaption,figure,picture,hr,img,li,main,ol,p,pre,ul,a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,rtc,ruby,s,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,embed,object,param,source,canvas,script,noscript,del,ins,caption,col,colgroup,table,thead,tbody,td,th,tr,button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,output,progress,select,textarea,details,dialog,menu,menuitem,summary,content,element,shadow,template,blockquote,iframe,tfoot"),zn=de("svg,animate,circle,clippath,cursor,defs,desc,ellipse,filter,font-face,foreignobject,g,glyph,image,line,marker,mask,missing-glyph,path,pattern,polygon,polyline,rect,switch,symbol,text,textpath,tspan,use,view",!0),Bi=function(e){return Ws(e)||zn(e)};function Hs(e){if(zn(e))return"svg";if(e==="math")return"math"}var Jt=Object.create(null);function Gs(e){if(!se)return!0;if(Bi(e))return!1;if(e=e.toLowerCase(),Jt[e]!=null)return Jt[e];var r=document.createElement(e);return e.indexOf("-")>-1?Jt[e]=r.constructor===window.HTMLUnknownElement||r.constructor===window.HTMLElement:Jt[e]=/HTMLUnknownElement/.test(r.toString())}var Un=de("text,number,password,search,email,tel,url");function Ys(e){if(typeof e=="string"){var r=document.querySelector(e);return r||document.createElement("div")}else return e}function Zs(e,r){var i=document.createElement(e);return e!=="select"||r.data&&r.data.attrs&&r.data.attrs.multiple!==void 0&&i.setAttribute("multiple","multiple"),i}function Xs(e,r){return document.createElementNS(Us[e],r)}function Ks(e){return document.createTextNode(e)}function qs(e){return document.createComment(e)}function Js(e,r,i){e.insertBefore(r,i)}function Qs(e,r){e.removeChild(r)}function Vs(e,r){e.appendChild(r)}function ef(e){return e.parentNode}function tf(e){return e.nextSibling}function nf(e){return e.tagName}function rf(e,r){e.textContent=r}function af(e,r){e.setAttribute(r,"")}var of=Object.freeze({__proto__:null,createElement:Zs,createElementNS:Xs,createTextNode:Ks,createComment:qs,insertBefore:Js,removeChild:Qs,appendChild:Vs,parentNode:ef,nextSibling:tf,tagName:nf,setTextContent:rf,setStyleScope:af}),sf={create:function(e,r){st(r)},update:function(e,r){e.data.ref!==r.data.ref&&(st(e,!0),st(r))},destroy:function(e){st(e,!0)}};function st(e,r){var i=e.data.ref;if(l(i)){var a=e.context,o=e.componentInstance||e.elm,s=r?null:o,f=r?void 0:o;if(y(i)){xe(i,a,[s],a,"template ref function");return}var c=e.data.refInFor,u=typeof i=="string"||typeof i=="number",A=te(i),_=a.$refs;if(u||A){if(c){var b=u?_[i]:i.value;r?g(b)&&Oe(b,o):g(b)?b.includes(o)||b.push(o):u?(_[i]=[o],ki(a,i,_[i])):i.value=[o]}else if(u){if(r&&_[i]!==o)return;_[i]=f,ki(a,i,s)}else if(A){if(r&&i.value!==o)return;i.value=s}}}}function ki(e,r,i){var a=e._setupState;a&&oe(a,r)&&(te(a[r])?a[r].value=i:a[r]=i)}var Me=new fe("",{},[]),xt=["create","activate","update","remove","destroy"];function Ye(e,r){return e.key===r.key&&e.asyncFactory===r.asyncFactory&&(e.tag===r.tag&&e.isComment===r.isComment&&l(e.data)===l(r.data)&&ff(e,r)||x(e.isAsyncPlaceholder)&&v(r.asyncFactory.error))}function ff(e,r){if(e.tag!=="input")return!0;var i,a=l(i=e.data)&&l(i=i.attrs)&&i.type,o=l(i=r.data)&&l(i=i.attrs)&&i.type;return a===o||Un(a)&&Un(o)}function cf(e,r,i){var a,o,s={};for(a=r;a<=i;++a)o=e[a].key,l(o)&&(s[o]=a);return s}function lf(e){var r,i,a={},o=e.modules,s=e.nodeOps;for(r=0;r<xt.length;++r)for(a[xt[r]]=[],i=0;i<o.length;++i)l(o[i][xt[r]])&&a[xt[r]].push(o[i][xt[r]]);function f(p){return new fe(s.tagName(p).toLowerCase(),{},[],void 0,p)}function c(p,d){function h(){--h.listeners===0&&u(p)}return h.listeners=d,h}function u(p){var d=s.parentNode(p);l(d)&&s.removeChild(d,p)}function A(p,d){return!d&&!p.ns&&!(re.ignoredElements.length&&re.ignoredElements.some(function(h){return he(h)?h.test(p.tag):h===p.tag}))&&re.isUnknownElement(p.tag)}var _=0;function b(p,d,h,E,T,F,j){if(l(p.elm)&&l(F)&&(p=F[j]=un(p)),p.isRootInsert=!T,!O(p,d,h,E)){var L=p.data,G=p.children,W=p.tag;l(W)?(p.elm=p.ns?s.createElementNS(p.ns,W):s.createElement(W,p),U(p),ce(p,G,d),l(L)&&Q(p,d),M(h,p.elm,E)):x(p.isComment)?(p.elm=s.createComment(p.text),M(h,p.elm,E)):(p.elm=s.createTextNode(p.text),M(h,p.elm,E))}}function O(p,d,h,E){var T=p.data;if(l(T)){var F=l(p.componentInstance)&&T.keepAlive;if(l(T=T.hook)&&l(T=T.init)&&T(p,!1),l(p.componentInstance))return k(p,d),M(h,p.elm,E),x(F)&&z(p,d,h,E),!0}}function k(p,d){l(p.data.pendingInsert)&&(d.push.apply(d,p.data.pendingInsert),p.data.pendingInsert=null),p.elm=p.componentInstance.$el,V(p)?(Q(p,d),U(p)):(st(p),d.push(p))}function z(p,d,h,E){for(var T,F=p;F.componentInstance;)if(F=F.componentInstance._vnode,l(T=F.data)&&l(T=T.transition)){for(T=0;T<a.activate.length;++T)a.activate[T](Me,F);d.push(F);break}M(h,p.elm,E)}function M(p,d,h){l(p)&&(l(h)?s.parentNode(h)===p&&s.insertBefore(p,d,h):s.appendChild(p,d))}function ce(p,d,h){if(g(d))for(var E=0;E<d.length;++E)b(d[E],h,p.elm,null,!0,d,E);else I(p.text)&&s.appendChild(p.elm,s.createTextNode(String(p.text)))}function V(p){for(;p.componentInstance;)p=p.componentInstance._vnode;return l(p.tag)}function Q(p,d){for(var h=0;h<a.create.length;++h)a.create[h](Me,p);r=p.data.hook,l(r)&&(l(r.create)&&r.create(Me,p),l(r.insert)&&d.push(p))}function U(p){var d;if(l(d=p.fnScopeId))s.setStyleScope(p.elm,d);else for(var h=p;h;)l(d=h.context)&&l(d=d.$options._scopeId)&&s.setStyleScope(p.elm,d),h=h.parent;l(d=He)&&d!==p.context&&d!==p.fnContext&&l(d=d.$options._scopeId)&&s.setStyleScope(p.elm,d)}function le(p,d,h,E,T,F){for(;E<=T;++E)b(h[E],F,p,d,!1,h,E)}function R(p){var d,h,E=p.data;if(l(E))for(l(d=E.hook)&&l(d=d.destroy)&&d(p),d=0;d<a.destroy.length;++d)a.destroy[d](p);if(l(d=p.children))for(h=0;h<p.children.length;++h)R(p.children[h])}function ue(p,d,h){for(;d<=h;++d){var E=p[d];l(E)&&(l(E.tag)?(ne(E),R(E)):u(E.elm))}}function ne(p,d){if(l(d)||l(p.data)){var h,E=a.remove.length+1;for(l(d)?d.listeners+=E:d=c(p.elm,E),l(h=p.componentInstance)&&l(h=h._vnode)&&l(h.data)&&ne(h,d),h=0;h<a.remove.length;++h)a.remove[h](p,d);l(h=p.data.hook)&&l(h=h.remove)?h(p,d):d()}else u(p.elm)}function be(p,d,h,E,T){for(var F=0,j=0,L=d.length-1,G=d[0],W=d[L],H=h.length-1,ee=h[0],ve=h[H],Xe,Ke,qe,lt,rr=!T;F<=L&&j<=H;)v(G)?G=d[++F]:v(W)?W=d[--L]:Ye(G,ee)?(Be(G,ee,E,h,j),G=d[++F],ee=h[++j]):Ye(W,ve)?(Be(W,ve,E,h,H),W=d[--L],ve=h[--H]):Ye(G,ve)?(Be(G,ve,E,h,H),rr&&s.insertBefore(p,G.elm,s.nextSibling(W.elm)),G=d[++F],ve=h[--H]):Ye(W,ee)?(Be(W,ee,E,h,j),rr&&s.insertBefore(p,W.elm,G.elm),W=d[--L],ee=h[++j]):(v(Xe)&&(Xe=cf(d,F,L)),Ke=l(ee.key)?Xe[ee.key]:rn(ee,d,F,L),v(Ke)?b(ee,E,p,G.elm,!1,h,j):(qe=d[Ke],Ye(qe,ee)?(Be(qe,ee,E,h,j),d[Ke]=void 0,rr&&s.insertBefore(p,qe.elm,G.elm)):b(ee,E,p,G.elm,!1,h,j)),ee=h[++j]);F>L?(lt=v(h[H+1])?null:h[H+1].elm,le(p,lt,h,j,H,E)):j>H&&ue(d,F,L)}function tr(p){for(var d={},h=0;h<p.length;h++){var E=p[h],T=E.key;l(T)&&(d[T]?Ae("Duplicate keys detected: '".concat(T,"'. This may cause an update error."),E.context):d[T]=!0)}}function rn(p,d,h,E){for(var T=h;T<E;T++){var F=d[T];if(l(F)&&Ye(p,F))return T}}function Be(p,d,h,E,T,F){if(p!==d){l(d.elm)&&l(E)&&(d=E[T]=un(d));var j=d.elm=p.elm;if(x(p.isAsyncPlaceholder)){l(d.asyncFactory.resolved)?ct(p.elm,d,h):d.isAsyncPlaceholder=!0;return}if(x(d.isStatic)&&x(p.isStatic)&&d.key===p.key&&(x(d.isCloned)||x(d.isOnce))){d.componentInstance=p.componentInstance;return}var L,G=d.data;l(G)&&l(L=G.hook)&&l(L=L.prepatch)&&L(p,d);var W=p.children,H=d.children;if(l(G)&&V(d)){for(L=0;L<a.update.length;++L)a.update[L](p,d);l(L=G.hook)&&l(L=L.update)&&L(p,d)}v(d.text)?l(W)&&l(H)?W!==H&&be(j,W,H,h,F):l(H)?(l(p.text)&&s.setTextContent(j,""),le(j,null,H,0,H.length-1,h)):l(W)?ue(W,0,W.length-1):l(p.text)&&s.setTextContent(j,""):p.text!==d.text&&s.setTextContent(j,d.text),l(G)&&l(L=G.hook)&&l(L=L.postpatch)&&L(p,d)}}function je(p,d,h){if(x(h)&&l(p.parent))p.parent.data.pendingInsert=d;else for(var E=0;E<d.length;++E)d[E].data.hook.insert(d[E])}var nr=!1,an=de("attrs,class,staticClass,staticStyle,key");function ct(p,d,h,E){var T,F=d.tag,j=d.data,L=d.children;if(E=E||j&&j.pre,d.elm=p,x(d.isComment)&&l(d.asyncFactory))return d.isAsyncPlaceholder=!0,!0;if(l(j)&&(l(T=j.hook)&&l(T=T.init)&&T(d,!0),l(T=d.componentInstance)))return k(d,h),!0;if(l(F)){if(l(L))if(!p.hasChildNodes())ce(d,L,h);else if(l(T=j)&&l(T=T.domProps)&&l(T=T.innerHTML)){if(T!==p.innerHTML)return!1}else{for(var G=!0,W=p.firstChild,H=0;H<L.length;H++){if(!W||!ct(W,L[H],h,E)){G=!1;break}W=W.nextSibling}if(!G||W)return!1}if(l(j)){var ee=!1;for(var ve in j)if(!an(ve)){ee=!0,Q(d,h);break}!ee&&j.class&&at(j.class)}}else p.data!==d.text&&(p.data=d.text);return!0}function Bt(p,d,h){return l(d.tag)?d.tag.indexOf("vue-component")===0||!A(d,h)&&d.tag.toLowerCase()===(p.tagName&&p.tagName.toLowerCase()):p.nodeType===(d.isComment?8:3)}return function(d,h,E,T){if(v(h)){l(d)&&R(d);return}var F=!1,j=[];if(v(d))F=!0,b(h,j);else{var L=l(d.nodeType);if(!L&&Ye(d,h))Be(d,h,j,null,null,T);else{if(L){if(d.nodeType===1&&d.hasAttribute(dr)&&(d.removeAttribute(dr),E=!0),x(E)&&ct(d,h,j))return je(h,j,!0),d;d=f(d)}var G=d.elm,W=s.parentNode(G);if(b(h,j,G._leaveCb?null:W,s.nextSibling(G)),l(h.parent))for(var H=h.parent,ee=V(h);H;){for(var ve=0;ve<a.destroy.length;++ve)a.destroy[ve](H);if(H.elm=h.elm,ee){for(var Xe=0;Xe<a.create.length;++Xe)a.create[Xe](Me,H);var Ke=H.data.hook.insert;if(Ke.merged)for(var qe=Ke.fns.slice(1),lt=0;lt<qe.length;lt++)qe[lt]()}else st(H);H=H.parent}l(W)?ue([d],0,0):l(d.tag)&&R(d)}}return je(h,j,F),h.elm}}var uf={create:Wn,update:Wn,destroy:function(r){Wn(r,Me)}};function Wn(e,r){(e.data.directives||r.data.directives)&&df(e,r)}function df(e,r){var i=e===Me,a=r===Me,o=Si(e.data.directives,e.context),s=Si(r.data.directives,r.context),f=[],c=[],u,A,_;for(u in s)A=o[u],_=s[u],A?(_.oldValue=A.value,_.oldArg=A.arg,wt(_,"update",r,e),_.def&&_.def.componentUpdated&&c.push(_)):(wt(_,"bind",r,e),_.def&&_.def.inserted&&f.push(_));if(f.length){var b=function(){for(var O=0;O<f.length;O++)wt(f[O],"inserted",r,e)};i?De(r,"insert",b):b()}if(c.length&&De(r,"postpatch",function(){for(var O=0;O<c.length;O++)wt(c[O],"componentUpdated",r,e)}),!i)for(u in o)s[u]||wt(o[u],"unbind",e,e,a)}var pf=Object.create(null);function Si(e,r){var i=Object.create(null);if(!e)return i;var a,o;for(a=0;a<e.length;a++){if(o=e[a],o.modifiers||(o.modifiers=pf),i[Af(o)]=o,r._setupState&&r._setupState.__sfc){var s=o.def||Zt(r,"_setupState","v-"+o.name);typeof s=="function"?o.def={bind:s,update:s}:o.def=s}o.def=o.def||Zt(r.$options,"directives",o.name,!0)}return i}function Af(e){return e.rawName||"".concat(e.name,".").concat(Object.keys(e.modifiers||{}).join("."))}function wt(e,r,i,a,o){var s=e.def&&e.def[r];if(s)try{s(i.elm,e,i,a,o)}catch(f){We(f,i.context,"directive ".concat(e.name," ").concat(r," hook"))}}var vf=[sf,uf];function Oi(e,r){var i=r.componentOptions;if(!(l(i)&&i.Ctor.options.inheritAttrs===!1)&&!(v(e.data.attrs)&&v(r.data.attrs))){var a,o,s,f=r.elm,c=e.data.attrs||{},u=r.data.attrs||{};(l(u.__ob__)||x(u._v_attr_proxy))&&(u=r.data.attrs=Y({},u));for(a in u)o=u[a],s=c[a],s!==o&&Ti(f,a,o,r.data.pre);(Qe||cn)&&u.value!==c.value&&Ti(f,"value",u.value);for(a in c)v(u[a])&&(Ln(a)?f.removeAttributeNS(jn,wi(a)):xi(a)||f.removeAttribute(a))}}function Ti(e,r,i,a){a||e.tagName.indexOf("-")>-1?Pi(e,r,i):js(r)?qt(i)?e.removeAttribute(r):(i=r==="allowfullscreen"&&e.tagName==="EMBED"?"true":r,e.setAttribute(r,i)):xi(r)?e.setAttribute(r,Ms(r,i)):Ln(r)?qt(i)?e.removeAttributeNS(jn,wi(r)):e.setAttributeNS(jn,r,i):Pi(e,r,i)}function Pi(e,r,i){if(qt(i))e.removeAttribute(r);else{if(Qe&&!Ve&&e.tagName==="TEXTAREA"&&r==="placeholder"&&i!==""&&!e.__ieph){var a=function(o){o.stopImmediatePropagation(),e.removeEventListener("input",a)};e.addEventListener("input",a),e.__ieph=!0}e.setAttribute(r,i)}}var hf={create:Oi,update:Oi};function $i(e,r){var i=r.elm,a=r.data,o=e.data;if(!(v(a.staticClass)&&v(a.class)&&(v(o)||v(o.staticClass)&&v(o.class)))){var s=Ls(r),f=i._transitionClasses;l(f)&&(s=Rn(s,Fn(f))),s!==i._prevClass&&(i.setAttribute("class",s),i._prevClass=s)}}var gf={create:$i,update:$i},Hn="__r",Gn="__c";function _f(e){if(l(e[Hn])){var r=Qe?"change":"input";e[r]=[].concat(e[Hn],e[r]||[]),delete e[Hn]}l(e[Gn])&&(e.change=[].concat(e[Gn],e.change||[]),delete e[Gn])}var Et;function mf(e,r,i){var a=Et;return function o(){var s=r.apply(null,arguments);s!==null&&Ii(e,o,i,a)}}var Cf=mn&&!(hr&&Number(hr[1])<=53);function bf(e,r,i,a){if(Cf){var o=oi,s=r;r=s._wrapper=function(f){if(f.target===f.currentTarget||f.timeStamp>=o||f.timeStamp<=0||f.target.ownerDocument!==document)return s.apply(this,arguments)}}Et.addEventListener(e,r,gr?{capture:i,passive:a}:i)}function Ii(e,r,i,a){(a||Et).removeEventListener(e,r._wrapper||r,i)}function Yn(e,r){if(!(v(e.data.on)&&v(r.data.on))){var i=r.data.on||{},a=e.data.on||{};Et=r.elm||e.elm,_f(i),Rr(i,a,bf,Ii,mf,r.context),Et=void 0}}var yf={create:Yn,update:Yn,destroy:function(e){return Yn(e,Me)}},Qt;function Di(e,r){if(!(v(e.data.domProps)&&v(r.data.domProps))){var i,a,o=r.elm,s=e.data.domProps||{},f=r.data.domProps||{};(l(f.__ob__)||x(f._v_attr_proxy))&&(f=r.data.domProps=Y({},f));for(i in s)i in f||(o[i]="");for(i in f){if(a=f[i],i==="textContent"||i==="innerHTML"){if(r.children&&(r.children.length=0),a===s[i])continue;o.childNodes.length===1&&o.removeChild(o.childNodes[0])}if(i==="value"&&o.tagName!=="PROGRESS"){o._value=a;var c=v(a)?"":String(a);xf(o,c)&&(o.value=c)}else if(i==="innerHTML"&&zn(o.tagName)&&v(o.innerHTML)){Qt=Qt||document.createElement("div"),Qt.innerHTML="<svg>".concat(a,"</svg>");for(var u=Qt.firstChild;o.firstChild;)o.removeChild(o.firstChild);for(;u.firstChild;)o.appendChild(u.firstChild)}else if(a!==s[i])try{o[i]=a}catch{}}}}function xf(e,r){return!e.composing&&(e.tagName==="OPTION"||wf(e,r)||Ef(e,r))}function wf(e,r){var i=!0;try{i=document.activeElement!==e}catch{}return i&&e.value!==r}function Ef(e,r){var i=e.value,a=e._vModifiers;if(l(a)){if(a.number)return Je(i)!==Je(r);if(a.trim)return i.trim()!==r.trim()}return i!==r}var Bf={create:Di,update:Di},kf=Le(function(e){var r={},i=/;(?![^(]*\))/g,a=/:(.+)/;return e.split(i).forEach(function(o){if(o){var s=o.split(a);s.length>1&&(r[s[0].trim()]=s[1].trim())}}),r});function Zn(e){var r=Ni(e.style);return e.staticStyle?Y(e.staticStyle,r):r}function Ni(e){return Array.isArray(e)?cr(e):typeof e=="string"?kf(e):e}function Sf(e,r){var i={},a;if(r)for(var o=e;o.componentInstance;)o=o.componentInstance._vnode,o&&o.data&&(a=Zn(o.data))&&Y(i,a);(a=Zn(e.data))&&Y(i,a);for(var s=e;s=s.parent;)s.data&&(a=Zn(s.data))&&Y(i,a);return i}var Of=/^--/,Mi=/\s*!important$/,ji=function(e,r,i){if(Of.test(r))e.style.setProperty(r,i);else if(Mi.test(i))e.style.setProperty(ut(r),i.replace(Mi,""),"important");else{var a=Tf(r);if(Array.isArray(i))for(var o=0,s=i.length;o<s;o++)e.style[a]=i[o];else e.style[a]=i}},Li=["Webkit","Moz","ms"],Vt,Tf=Le(function(e){if(Vt=Vt||document.createElement("div").style,e=Re(e),e!=="filter"&&e in Vt)return e;for(var r=e.charAt(0).toUpperCase()+e.slice(1),i=0;i<Li.length;i++){var a=Li[i]+r;if(a in Vt)return a}});function Ri(e,r){var i=r.data,a=e.data;if(!(v(i.staticStyle)&&v(i.style)&&v(a.staticStyle)&&v(a.style))){var o,s,f=r.elm,c=a.staticStyle,u=a.normalizedStyle||a.style||{},A=c||u,_=Ni(r.data.style)||{};r.data.normalizedStyle=l(_.__ob__)?Y({},_):_;var b=Sf(r,!0);for(s in A)v(b[s])&&ji(f,s,"");for(s in b)o=b[s],ji(f,s,o??"")}}var Pf={create:Ri,update:Ri},Fi=/\s+/;function zi(e,r){if(!(!r||!(r=r.trim())))if(e.classList)r.indexOf(" ")>-1?r.split(Fi).forEach(function(a){return e.classList.add(a)}):e.classList.add(r);else{var i=" ".concat(e.getAttribute("class")||""," ");i.indexOf(" "+r+" ")<0&&e.setAttribute("class",(i+r).trim())}}function Ui(e,r){if(!(!r||!(r=r.trim())))if(e.classList)r.indexOf(" ")>-1?r.split(Fi).forEach(function(o){return e.classList.remove(o)}):e.classList.remove(r),e.classList.length||e.removeAttribute("class");else{for(var i=" ".concat(e.getAttribute("class")||""," "),a=" "+r+" ";i.indexOf(a)>=0;)i=i.replace(a," ");i=i.trim(),i?e.setAttribute("class",i):e.removeAttribute("class")}}function Wi(e){if(e){if(typeof e=="object"){var r={};return e.css!==!1&&Y(r,Hi(e.name||"v")),Y(r,e),r}else if(typeof e=="string")return Hi(e)}}var Hi=Le(function(e){return{enterClass:"".concat(e,"-enter"),enterToClass:"".concat(e,"-enter-to"),enterActiveClass:"".concat(e,"-enter-active"),leaveClass:"".concat(e,"-leave"),leaveToClass:"".concat(e,"-leave-to"),leaveActiveClass:"".concat(e,"-leave-active")}}),Gi=se&&!Ve,ft="transition",Xn="animation",en="transition",tn="transitionend",Kn="animation",Yi="animationend";Gi&&(window.ontransitionend===void 0&&window.onwebkittransitionend!==void 0&&(en="WebkitTransition",tn="webkitTransitionEnd"),window.onanimationend===void 0&&window.onwebkitanimationend!==void 0&&(Kn="WebkitAnimation",Yi="webkitAnimationEnd"));var Zi=se?window.requestAnimationFrame?window.requestAnimationFrame.bind(window):setTimeout:function(e){return e()};function Xi(e){Zi(function(){Zi(e)})}function Ze(e,r){var i=e._transitionClasses||(e._transitionClasses=[]);i.indexOf(r)<0&&(i.push(r),zi(e,r))}function Ee(e,r){e._transitionClasses&&Oe(e._transitionClasses,r),Ui(e,r)}function Ki(e,r,i){var a=qi(e,r),o=a.type,s=a.timeout,f=a.propCount;if(!o)return i();var c=o===ft?tn:Yi,u=0,A=function(){e.removeEventListener(c,_),i()},_=function(b){b.target===e&&++u>=f&&A()};setTimeout(function(){u<f&&A()},s+1),e.addEventListener(c,_)}var $f=/\b(transform|all)(,|$)/;function qi(e,r){var i=window.getComputedStyle(e),a=(i[en+"Delay"]||"").split(", "),o=(i[en+"Duration"]||"").split(", "),s=Ji(a,o),f=(i[Kn+"Delay"]||"").split(", "),c=(i[Kn+"Duration"]||"").split(", "),u=Ji(f,c),A,_=0,b=0;r===ft?s>0&&(A=ft,_=s,b=o.length):r===Xn?u>0&&(A=Xn,_=u,b=c.length):(_=Math.max(s,u),A=_>0?s>u?ft:Xn:null,b=A?A===ft?o.length:c.length:0);var O=A===ft&&$f.test(i[en+"Property"]);return{type:A,timeout:_,propCount:b,hasTransform:O}}function Ji(e,r){for(;e.length<r.length;)e=e.concat(e);return Math.max.apply(null,r.map(function(i,a){return Qi(i)+Qi(e[a])}))}function Qi(e){return Number(e.slice(0,-1).replace(",","."))*1e3}function qn(e,r){var i=e.elm;l(i._leaveCb)&&(i._leaveCb.cancelled=!0,i._leaveCb());var a=Wi(e.data.transition);if(!v(a)&&!(l(i._enterCb)||i.nodeType!==1)){for(var o=a.css,s=a.type,f=a.enterClass,c=a.enterToClass,u=a.enterActiveClass,A=a.appearClass,_=a.appearToClass,b=a.appearActiveClass,O=a.beforeEnter,k=a.enter,z=a.afterEnter,M=a.enterCancelled,ce=a.beforeAppear,V=a.appear,Q=a.afterAppear,U=a.appearCancelled,le=a.duration,R=He,ue=He.$vnode;ue&&ue.parent;)R=ue.context,ue=ue.parent;var ne=!R._isMounted||!e.isRootInsert;if(!(ne&&!V&&V!=="")){var be=ne&&A?A:f,tr=ne&&b?b:u,rn=ne&&_?_:c,Be=ne&&ce||O,je=ne&&y(V)?V:k,nr=ne&&Q||z,an=ne&&U||M,ct=Je($(le)?le.enter:le),Bt=o!==!1&&!Ve,p=Jn(je),d=i._enterCb=St(function(){Bt&&(Ee(i,rn),Ee(i,tr)),d.cancelled?(Bt&&Ee(i,be),an&&an(i)):nr&&nr(i),i._enterCb=null});e.data.show||De(e,"insert",function(){var h=i.parentNode,E=h&&h._pending&&h._pending[e.key];E&&E.tag===e.tag&&E.elm._leaveCb&&E.elm._leaveCb(),je&&je(i,d)}),Be&&Be(i),Bt&&(Ze(i,be),Ze(i,tr),Xi(function(){Ee(i,be),d.cancelled||(Ze(i,rn),p||(ea(ct)?setTimeout(d,ct):Ki(i,s,d)))})),e.data.show&&(r&&r(),je&&je(i,d)),!Bt&&!p&&d()}}}function Vi(e,r){var i=e.elm;l(i._enterCb)&&(i._enterCb.cancelled=!0,i._enterCb());var a=Wi(e.data.transition);if(v(a)||i.nodeType!==1)return r();if(l(i._leaveCb))return;var o=a.css,s=a.type,f=a.leaveClass,c=a.leaveToClass,u=a.leaveActiveClass,A=a.beforeLeave,_=a.leave,b=a.afterLeave,O=a.leaveCancelled,k=a.delayLeave,z=a.duration,M=o!==!1&&!Ve,ce=Jn(_),V=Je($(z)?z.leave:z),Q=i._leaveCb=St(function(){i.parentNode&&i.parentNode._pending&&(i.parentNode._pending[e.key]=null),M&&(Ee(i,c),Ee(i,u)),Q.cancelled?(M&&Ee(i,f),O&&O(i)):(r(),b&&b(i)),i._leaveCb=null});k?k(U):U();function U(){Q.cancelled||(!e.data.show&&i.parentNode&&((i.parentNode._pending||(i.parentNode._pending={}))[e.key]=e),A&&A(i),M&&(Ze(i,f),Ze(i,u),Xi(function(){Ee(i,f),Q.cancelled||(Ze(i,c),ce||(ea(V)?setTimeout(Q,V):Ki(i,s,Q)))})),_&&_(i,Q),!M&&!ce&&Q())}}function Yc(e,r,i){typeof e!="number"?Ae("<transition> explicit ".concat(r," duration is not a valid number - ")+"got ".concat(JSON.stringify(e),"."),i.context):isNaN(e)&&Ae("<transition> explicit ".concat(r," duration is NaN - ")+"the duration expression might be incorrect.",i.context)}function ea(e){return typeof e=="number"&&!isNaN(e)}function Jn(e){if(v(e))return!1;var r=e.fns;return l(r)?Jn(Array.isArray(r)?r[0]:r):(e._length||e.length)>1}function ta(e,r){r.data.show!==!0&&qn(r)}var If=se?{create:ta,activate:ta,remove:function(e,r){e.data.show!==!0?Vi(e,r):r()}}:{},Df=[hf,gf,yf,Bf,Pf,If],Nf=Df.concat(vf),Mf=lf({nodeOps:of,modules:Nf});Ve&&document.addEventListener("selectionchange",function(){var e=document.activeElement;e&&e.vmodel&&Qn(e,"input")});var na={inserted:function(e,r,i,a){i.tag==="select"?(a.elm&&!a.elm._vOptions?De(i,"postpatch",function(){na.componentUpdated(e,r,i)}):ra(e,r,i.context),e._vOptions=[].map.call(e.options,nn)):(i.tag==="textarea"||Un(e.type))&&(e._vModifiers=r.modifiers,r.modifiers.lazy||(e.addEventListener("compositionstart",jf),e.addEventListener("compositionend",oa),e.addEventListener("change",oa),Ve&&(e.vmodel=!0)))},componentUpdated:function(e,r,i){if(i.tag==="select"){ra(e,r,i.context);var a=e._vOptions,o=e._vOptions=[].map.call(e.options,nn);if(o.some(function(f,c){return!Fe(f,a[c])})){var s=e.multiple?r.value.some(function(f){return aa(f,o)}):r.value!==r.oldValue&&aa(r.value,o);s&&Qn(e,"change")}}}};function ra(e,r,i){ia(e,r,i),(Qe||cn)&&setTimeout(function(){ia(e,r,i)},0)}function ia(e,r,i){var a=r.value,o=e.multiple;if(!(o&&!Array.isArray(a))){for(var s,f,c=0,u=e.options.length;c<u;c++)if(f=e.options[c],o)s=ur(a,nn(f))>-1,f.selected!==s&&(f.selected=s);else if(Fe(nn(f),a)){e.selectedIndex!==c&&(e.selectedIndex=c);return}o||(e.selectedIndex=-1)}}function aa(e,r){return r.every(function(i){return!Fe(i,e)})}function nn(e){return"_value"in e?e._value:e.value}function jf(e){e.target.composing=!0}function oa(e){e.target.composing&&(e.target.composing=!1,Qn(e.target,"input"))}function Qn(e,r){var i=document.createEvent("HTMLEvents");i.initEvent(r,!0,!0),e.dispatchEvent(i)}function Vn(e){return e.componentInstance&&(!e.data||!e.data.transition)?Vn(e.componentInstance._vnode):e}var Lf={bind:function(e,r,i){var a=r.value;i=Vn(i);var o=i.data&&i.data.transition,s=e.__vOriginalDisplay=e.style.display==="none"?"":e.style.display;a&&o?(i.data.show=!0,qn(i,function(){e.style.display=s})):e.style.display=a?s:"none"},update:function(e,r,i){var a=r.value,o=r.oldValue;if(!a!=!o){i=Vn(i);var s=i.data&&i.data.transition;s?(i.data.show=!0,a?qn(i,function(){e.style.display=e.__vOriginalDisplay}):Vi(i,function(){e.style.display="none"})):e.style.display=a?e.__vOriginalDisplay:"none"}},unbind:function(e,r,i,a,o){o||(e.style.display=e.__vOriginalDisplay)}},Rf={model:na,show:Lf},sa={name:String,appear:Boolean,css:Boolean,mode:String,type:String,enterClass:String,leaveClass:String,enterToClass:String,leaveToClass:String,enterActiveClass:String,leaveActiveClass:String,appearClass:String,appearActiveClass:String,appearToClass:String,duration:[Number,String,Object]};function er(e){var r=e&&e.componentOptions;return r&&r.Ctor.options.abstract?er(Kr(r.children)):e}function fa(e){var r={},i=e.$options;for(var a in i.propsData)r[a]=e[a];var o=i._parentListeners;for(var a in o)r[Re(a)]=o[a];return r}function ca(e,r){if(/\d-keep-alive$/.test(r.tag))return e("keep-alive",{props:r.componentOptions.propsData})}function Ff(e){for(;e=e.parent;)if(e.data.transition)return!0}function zf(e,r){return r.key===e.key&&r.tag===e.tag}var Uf=function(e){return e.tag||ht(e)},Wf=function(e){return e.name==="show"},Hf={name:"transition",props:sa,abstract:!0,render:function(e){var r=this,i=this.$slots.default;if(i&&(i=i.filter(Uf),!!i.length)){var a=this.mode,o=i[0];if(Ff(this.$vnode))return o;var s=er(o);if(!s)return o;if(this._leaving)return ca(e,o);var f="__transition-".concat(this._uid,"-");s.key=s.key==null?s.isComment?f+"comment":f+s.tag:I(s.key)?String(s.key).indexOf(f)===0?s.key:f+s.key:s.key;var c=(s.data||(s.data={})).transition=fa(this),u=this._vnode,A=er(u);if(s.data.directives&&s.data.directives.some(Wf)&&(s.data.show=!0),A&&A.data&&!zf(s,A)&&!ht(A)&&!(A.componentInstance&&A.componentInstance._vnode.isComment)){var _=A.data.transition=Y({},c);if(a==="out-in")return this._leaving=!0,De(_,"afterLeave",function(){r._leaving=!1,r.$forceUpdate()}),ca(e,o);if(a==="in-out"){if(ht(s))return u;var b,O=function(){b()};De(c,"afterEnter",O),De(c,"enterCancelled",O),De(_,"delayLeave",function(k){b=k})}}return o}}},la=Y({tag:String,moveClass:String},sa);delete la.mode;var Gf={props:la,beforeMount:function(){var e=this,r=this._update;this._update=function(i,a){var o=ri(e);e.__patch__(e._vnode,e.kept,!1,!0),e._vnode=e.kept,o(),r.call(e,i,a)}},render:function(e){for(var r=this.tag||this.$vnode.data.tag||"span",i=Object.create(null),a=this.prevChildren=this.children,o=this.$slots.default||[],s=this.children=[],f=fa(this),c=0;c<o.length;c++){var u=o[c];if(u.tag){if(u.key!=null&&String(u.key).indexOf("__vlist")!==0)s.push(u),i[u.key]=u,(u.data||(u.data={})).transition=f;else if(0)var A,_}}if(a){for(var b=[],O=[],c=0;c<a.length;c++){var u=a[c];u.data.transition=f,u.data.pos=u.elm.getBoundingClientRect(),i[u.key]?b.push(u):O.push(u)}this.kept=e(r,null,b),this.removed=O}return e(r,null,s)},updated:function(){var e=this.prevChildren,r=this.moveClass||(this.name||"v")+"-move";!e.length||!this.hasMove(e[0].elm,r)||(e.forEach(Yf),e.forEach(Zf),e.forEach(Xf),this._reflow=document.body.offsetHeight,e.forEach(function(i){if(i.data.moved){var a=i.elm,o=a.style;Ze(a,r),o.transform=o.WebkitTransform=o.transitionDuration="",a.addEventListener(tn,a._moveCb=function s(f){f&&f.target!==a||(!f||/transform$/.test(f.propertyName))&&(a.removeEventListener(tn,s),a._moveCb=null,Ee(a,r))})}}))},methods:{hasMove:function(e,r){if(!Gi)return!1;if(this._hasMove)return this._hasMove;var i=e.cloneNode();e._transitionClasses&&e._transitionClasses.forEach(function(o){Ui(i,o)}),zi(i,r),i.style.display="none",this.$el.appendChild(i);var a=qi(i);return this.$el.removeChild(i),this._hasMove=a.hasTransform}}};function Yf(e){e.elm._moveCb&&e.elm._moveCb(),e.elm._enterCb&&e.elm._enterCb()}function Zf(e){e.data.newPos=e.elm.getBoundingClientRect()}function Xf(e){var r=e.data.pos,i=e.data.newPos,a=r.left-i.left,o=r.top-i.top;if(a||o){e.data.moved=!0;var s=e.elm.style;s.transform=s.WebkitTransform="translate(".concat(a,"px,").concat(o,"px)"),s.transitionDuration="0s"}}var Kf={Transition:Hf,TransitionGroup:Gf};J.config.mustUseProp=Ds,J.config.isReservedTag=Bi,J.config.isReservedAttr=$s,J.config.getTagNamespace=Hs,J.config.isUnknownElement=Gs,Y(J.options.directives,Rf),Y(J.options.components,Kf),J.prototype.__patch__=se?Mf:X,J.prototype.$mount=function(e,r){return e=e&&se?Ys(e):void 0,No(this,e,r)},se&&setTimeout(function(){re.devtools&&Pt&&Pt.emit("init",J)},0)},983:C=>{C.exports="data:image/svg+xml,%3csvg%20viewBox=%270%200%2016%2016%27%20height=%2716%27%20width=%2716%27%20xmlns=%27http://www.w3.org/2000/svg%27%20xml:space=%27preserve%27%20style=%27fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2%27%3e%3cpath%20d=%27M6.4%2019%205%2017.6l5.6-5.6L5%206.4%206.4%205l5.6%205.6L17.6%205%2019%206.4%2013.4%2012l5.6%205.6-1.4%201.4-5.6-5.6L6.4%2019Z%27%20style=%27fill-rule:nonzero%27%20transform=%27matrix%28.85714%200%200%20.85714%20-2.286%20-2.286%29%27/%3e%3c/svg%3e"},1391:C=>{C.exports="data:image/svg+xml,%3csvg%20viewBox=%270%200%2016%2016%27%20height=%2716%27%20width=%2716%27%20xmlns=%27http://www.w3.org/2000/svg%27%20xml:space=%27preserve%27%20style=%27fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2%27%3e%3cpath%20d=%27M6.4%2019%205%2017.6l5.6-5.6L5%206.4%206.4%205l5.6%205.6L17.6%205%2019%206.4%2013.4%2012l5.6%205.6-1.4%201.4-5.6-5.6L6.4%2019Z%27%20style=%27fill:%23fff;fill-rule:nonzero%27%20transform=%27matrix%28.85714%200%200%20.85714%20-2.286%20-2.286%29%27/%3e%3c/svg%3e"}},ar={};function S(C){var w=ar[C];if(w!==void 0)return w.exports;var m=ar[C]={id:C,loaded:!1,exports:{}};return ir[C].call(m.exports,m,m.exports,S),m.loaded=!0,m.exports}S.m=ir,S.n=C=>{var w=C&&C.__esModule?()=>C.default:()=>C;return S.d(w,{a:w}),w},S.d=(C,w)=>{for(var m in w)S.o(w,m)&&!S.o(C,m)&&Object.defineProperty(C,m,{enumerable:!0,get:w[m]})},S.f={},S.e=C=>Promise.all(Object.keys(S.f).reduce((w,m)=>(S.f[m](C,w),w),[])),S.u=C=>"notifications-"+C+".js?v="+{"vendors-node_modules_core-js_modules_es_string_replace_js-node_modules_nextcloud_vue_dist_chu-8dd16e":"8c90abd3f932f6303c25","vendors-node_modules_nextcloud_notify_push_dist_index_js-node_modules_howler_dist_howler_js-n-993dcb":"44d808b0f86ad7bea909",src_NotificationsApp_vue:"0e479c896b339ba0512c","node_modules_nextcloud_dialogs_dist_chunks_index-CYiQsZoY_mjs":"e1eae20a1a7c60777163","vendors-node_modules_nextcloud_dialogs_dist_chunks_FilePicker-DUbP4INd_mjs":"b6755101e46d243727bf","data_image_svg_xml_3c_21--_20-_20SPDX-FileCopyrightText_202020_20Google_20Inc_20-_20SPDX-Lice-cc29b1":"90dd259ed383221e8104"}[C],S.g=function(){if(typeof globalThis=="object")return globalThis;try{return this||new Function("return this")()}catch{if(typeof window=="object")return window}}(),S.o=(C,w)=>Object.prototype.hasOwnProperty.call(C,w),(()=>{var C={},w="notifications:";S.l=(m,B,g,v)=>{if(C[m]){C[m].push(B);return}var l,x;if(g!==void 0)for(var N=document.getElementsByTagName("script"),I=0;I<N.length;I++){var y=N[I];if(y.getAttribute("src")==m||y.getAttribute("data-webpack")==w+g){l=y;break}}l||(x=!0,l=document.createElement("script"),l.charset="utf-8",l.timeout=120,S.nc&&l.setAttribute("nonce",S.nc),l.setAttribute("data-webpack",w+g),l.src=m),C[m]=[B];var $=(P,D)=>{l.onerror=l.onload=null,clearTimeout(Z);var he=C[m];if(delete C[m],l.parentNode&&l.parentNode.removeChild(l),he&&he.forEach(ge=>ge(D)),P)return P(D)},Z=setTimeout($.bind(null,void 0,{type:"timeout",target:l}),12e4);l.onerror=$.bind(null,l.onerror),l.onload=$.bind(null,l.onload),x&&document.head.appendChild(l)}})(),S.r=C=>{typeof Symbol<"u"&&Symbol.toStringTag&&Object.defineProperty(C,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(C,"__esModule",{value:!0})},S.nmd=C=>(C.paths=[],C.children||(C.children=[]),C),S.p="/apps/notifications/js/",(()=>{S.b=document.baseURI||self.location.href;var C={main:0};S.f.j=(B,g)=>{var v=S.o(C,B)?C[B]:void 0;if(v!==0)if(v)g.push(v[2]);else{var l=new Promise((y,$)=>v=C[B]=[y,$]);g.push(v[2]=l);var x=S.p+S.u(B),N=new Error,I=y=>{if(S.o(C,B)&&(v=C[B],v!==0&&(C[B]=void 0),v)){var $=y&&(y.type==="load"?"missing":y.type),Z=y&&y.target&&y.target.src;N.message="Loading chunk "+B+` failed.
(`+$+": "+Z+")",N.name="ChunkLoadError",N.type=$,N.request=Z,v[1](N)}};S.l(x,I,"chunk-"+B,B)}};var w=(B,g)=>{var[v,l,x]=g,N,I,y=0;if(v.some(Z=>C[Z]!==0)){for(N in l)S.o(l,N)&&(S.m[N]=l[N]);if(x)var $=x(S)}for(B&&B(g);y<v.length;y++)I=v[y],S.o(C,I)&&C[I]&&C[I][0](),C[I]=0},m=self.webpackChunknotifications=self.webpackChunknotifications||[];m.forEach(w.bind(null,0)),m.push=w.bind(null,m.push.bind(m))})(),S.nc=void 0;var qf={};(()=>{var C=S(144),w=S(3379),m=S.n(w),B=S(7795),g=S.n(B),v=S(569),l=S.n(v),x=S(3565),N=S.n(x),I=S(9216),y=S.n(I),$=S(4589),Z=S.n($),P=S(9137),D={};D.styleTagTransform=Z(),D.setAttributes=N(),D.insert=l().bind(null,"head"),D.domAPI=g(),D.insertStyleElement=y();var he=m()(P.Z,D);const ge=P.Z&&P.Z.locals?P.Z.locals:void 0;var ke=S(1473),Se={};Se.styleTagTransform=Z(),Se.setAttributes=N(),Se.insert=l().bind(null,"head"),Se.domAPI=g(),Se.insertStyleElement=y();var or=m()(ke.Z,Se);const Je=ke.Z&&ke.Z.locals?ke.Z.locals:void 0;C.ZP.prototype.t=t,C.ZP.prototype.n=n,C.ZP.prototype.OC=OC,C.ZP.prototype.OCA=OCA,S.nc=btoa(OC.requestToken),S.p=OC.linkTo("notifications","js/");const de=new C.ZP({el:"#notifications",name:"NotificationsApp",components:{NotificationsApp:()=>Promise.all([S.e("vendors-node_modules_core-js_modules_es_string_replace_js-node_modules_nextcloud_vue_dist_chu-8dd16e"),S.e("vendors-node_modules_nextcloud_notify_push_dist_index_js-node_modules_howler_dist_howler_js-n-993dcb"),S.e("src_NotificationsApp_vue")]).then(S.bind(S,5578))},render:on=>on("NotificationsApp")})})()})();})();

//# sourceMappingURL=notifications-main.js.map?v=7c3467867a94a26c00c8