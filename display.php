<?php
session_start();
include('./assets/header.php');
$input = $_SESSION['input'];
$flow_descriptions = array(
    'authorization_code' =>
    array(
        'name' => 'Authorization Code',
        'message' => 'For this flow, we need to first make an request at <b><u>https://' . $input['host_name'] . $input['route_path'] . '/oauth2/authorize</b></u> to get an authorization code first. After getting the authorization code, we can use it to request access token at <b><u>https://' . $input['host_name'] . $input['route_path'] . '/oauth2/token</b></u> endpoint.',
    ),
    'client_credentials' =>
    array(
        'name' => 'Client Credentials',
        'message' => 'This flow is mainly used for machine to machine. Hence it only returns an access token and your client must request a new token when the old one expired at <b><u>https://' . $input['host_name'] . $input['route_path'] . '/oauth2/token</b></u> endpoint.',
    ),
    'password' =>
    array(
        'name' => 'Password Grant',
        'message' => 'Use this flow <b>ONLY</b> when you have an ID verification in front. You need to provide the authenticated user id to this flow, then it will return an access token and refresh token at <b><u>https://' . $input['host_name'] . $input['route_path'] . '/oauth2/token</b></u> endpoint.',
    ),
    'implicit' =>
    array(
        'name' => 'Implicit Grant',
        'message' => 'You might never want to use this flow for security reasons. You can read more at this okta <a href="https://developer.okta.com/blog/2019/08/22/okta-authjs-pkce#why-you-should-never-use-the-implicit-flow-again">blog</a>. This flow only requires sending the client ID to authorization server to get an access token at <b><u>https://' . $input['host_name'] . $input['route_path'] . '/oauth2/authorize</b></u> endpoint. (In theory this flow should only be used to obtain ID Token to log in, NOT to consume API.)',
    ),
);

?>
<main>
    <div class="container">
        <div class="row">
            <div class="col l10 m12 s12 offset-l1">
                <div class="card z-depth-2" id="result">
                    <div class="row">
                        <div class="col s6">
                            <h5 class="section-title">Oauth2 Response</h5>
                        </div>
                    </div>
                    <div class="card-action" id='result_content'>
            <?php if(isset($_SESSION['error']) AND $_SESSION['error']=true){?>
                        <h4>Error: <?php echo $_SESSION['error_response']['error'];?> </h4>
                        <p id='result_message'><?php print '<pre>';print_r($_SESSION['error_response']['error_description']);print '</pre>';?></p>
            <?php }else{ ?>
                        <h4><?echo $flow_descriptions[$input['grant_type']]['name'].' flow:'; ?></h4>
                        <p id='result_message'><?echo $flow_descriptions[$input['grant_type']]['message']; ?></p>
                        <table class="striped responsive-table" id="producttable">
                            <tbody>
                                <tr>
                                    <th class="record">Access Token</th>
                                    <td><?echo $_SESSION['access_token']?></td>
                                </tr>
                                <?php if ($input['grant_type'] === 'implicit' || $input['grant_type'] === 'authorization_code') {?>
                                <tr>
                                    <th class="record">Authorized URL</th>
                                    <td><?echo $_SESSION['authroized_url']?></td>
                                </tr>
                                <tr>
                                    <th class="record">Authorized Token</th>
                                    <td><?echo $_SESSION['authorize_code']?></td>
                                </tr>
                                <tr>
                                    <th class="record">PKCE Verifier</th>
                                    <td><?echo $_SESSION['code_verifier']?></td>
                                </tr>
                                <tr>
                                    <th class="record">PKCE Challenge</th>
                                    <td><?echo $_SESSION['code_challenge']?></td>
                                </tr>
                                <?php } 
                                if ($input['grant_type'] === 'authorization_code' || $input['grant_type'] === "password") {
                                ?>
                                <tr>
                                    <th class="record">Refresh Token</th>
                                    <td><?echo $_SESSION['refresh_token']?></td>
                                </tr>
                                <? } ?>
                                <tr>
                                    <th class="record">TTL</th>
                                    <td><?echo $_SESSION['ttl']?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php if(isset($_SESSION['refresh_token']) AND !empty($_SESSION['refresh_token'])){?>
                <div class="right-align" id="btn_start_display" style="display:block">
                    <a href="./curl.php?type=refresh" class="btn waves-effect waves-light align-right blue darken-2">Refresh Token</a>
                </div>
                <?php }
            }
            ?>
            </div>
        </div>
    </div>
</main>
<?php
include('./assets/footer.php');