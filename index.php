<?php
session_start();
include('./assets/header.php');
?>
<main>
    <div class="container">
        <div class="row">
            <form action="curl.php" method="POST">
                <div class="col l10 m12 s12 offset-l1">
                    <div class="card z-depth-2">
                        <div class="row">
                            <div class="col s6">
                                <h5 class="section-title">Grant Flow</h5>
                            </div>
                            <div class="col s6 swtich-url right-align">
                                <div class="switch">
                                    <label>
                                        Dev Mode?
                                        <input id="dev-mode" name="dev_mode" type="checkbox" checked>
                                        <span class="lever" onclick="checkStatus()"></span>
                                        <text id='dev-text'>Yes</text>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-action">
                            <div class="row" id='grant_type'>
                                <div class="col l3 m6 s12">
                                    <label>
                                        <input name="grant_type" class="with-gap" type="radio" value="authorization_code" checked />
                                        <span>Authorization Code</span>
                                    </label>
                                </div>
                                <div class="col l3 m6 s12">
                                    <label>
                                        <input name="grant_type" class="with-gap" type="radio" value="client_credentials" />
                                        <span>Client Credential</span>
                                    </label>
                                </div>
                                <div class="col l3 m6 s12">
                                    <label>
                                        <input name="grant_type" class="with-gap" type="radio" value="password" />
                                        <span>Password Grant</span>
                                    </label>
                                </div>
                                <div class="col l3 m6 s12">
                                    <label>
                                        <input name="grant_type" class="with-gap" type="radio" value="implicit" />
                                        <span>Implicit Grant</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card z-depth-2" id="server_detail">
                        <div class="row">
                            <div class="col">
                                <h5 class="section-title">Oauth2 Server Detail</h5>
                            </div>
                        </div>
                        <div class="card-action">
                            <div class="row">
                                <div class="input-field col l4 m12 s12">
                                    <input name="host_name" id="host_name" type="text" required="" aria-required="true" autocomplete="off">
                                    <label for="host_name">Host Name</label>
                                    <span class="helper-text" data-error="wrong" data-success="right">eg: localhost:8443</span>
                                </div>
                                <div class="input-field col l4 m12 s12">
                                    <input name="route_path" id="route_path" type="text" required="" aria-required="true" autocomplete="off">
                                    <label for="route_path">Route Path</label>
                                    <span class="helper-text" data-error="wrong" data-success="right">eg: /test</span>
                                </div>
                                <div class="input-field col l4 m12 s12">
                                    <input name="scope" id="scope" type="text" required="" aria-required="true" autocomplete="off">
                                    <label for="scope">Scope</label>
                                    <span class="helper-text" data-error="wrong" data-success="right">eg: profile email</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col l6 s12">
                                    <input name="client_id" id="client_id" type="text" required="" aria-required="true" autocomplete="off">
                                    <label for="client_id">Client ID</label>
                                </div>
                                <div class="input-field col l6 s12">
                                    <input name="client_secret" id="client_secret" type="text" required="" aria-required="true" autocomplete="off">
                                    <label for="client_secret">Client Secret</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="input-field col l6 s12">
                                    <input name="provision_key" id="provision_key" type="text" required="" aria-required="true" autocomplete="off">
                                    <label for="provision_key">Provision Key</label>
                                </div>
                                <div class="input-field col l6 s12">
                                    <input name="authenticated_userid" id="authenticated_userid" type="text" required="" aria-required="true" autocomplete="off">
                                    <label for="authenticated_userid">AuthenticateA User ID</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card z-depth-2" id="result" style="display:none">
                        <div class="row">
                            <div class="col s6">
                                <h5 class="section-title">Oauth2 Response</h5>
                            </div>
                        </div>
                        <div class="card-action" id='result_content'>
                            <h4 id='result_head'></h4>
                            <p id="result_message"></p>
                            <table class="striped responsive-table" id="producttable">
                                <tbody>
                                </tbody>
                            </table>
                            <template id="productrow">
                                <tr>
                                    <th class="record"></th>
                                    <td></td>
                                </tr>
                            </template>
                        </div>
                    </div>
                    <div class="right-align" id="btn_start_display" style="display:block">
                        <button id="btn_start" class="btn waves-effect waves-light align-right blue darken-2" type="submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>
<?php
session_destroy();
include('./assets/footer.php');
?>
<script>
    function checkStatus() {
        if (document.getElementById("dev-mode").checked) {
            document.getElementById('dev-text').innerHTML = 'No';
        } else {
            document.getElementById('dev-text').innerHTML = 'Yes';
        }
    }
</script>