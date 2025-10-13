<?php

/**
 * @@ EXAMPLES @@
 *
 * Pattern: fuck\.(.*)
 *      Ban: fuck.tv; fuck.com; any-fuck.com;
 *      Not Ban: fuck-any.com
 * Pattern: ^fuck\.(.*)$
 *      Ban: fuck.tv; fuck.com; fuck.com.br;
 *      Not Ban: fuck-any.com; any-fuck.com; any-fuck-any.com
 * Pattern: ^fuck\.com$
 *      Ban: fuck.com ONLY
 *      Not Ban: fuck.tv; fuck.net; any-fuck.any; any-fuck-any.com
 * Pattern: fuck
 *      Ban: any domain/extension that has "fuck"
 * Pattern: (.*)fuck(.*)\.com
 *      Ban: any-fuck-any.com
 *      Not Ban: any-fuck.com; fuck-any.com; fuck.com, any-fuck-any.net
 */

return array(
    '^ruporn\.(.*)$',
    '^russiaxxx\.(.*)$',
    '^lassoslabs\.(.*)$',
    '^xvideosanal\.(.*)$',
    '^bioskopkismin\.(.*)$',
    '^adultcambabes\.(.*)',
    'porno',
);
