<?php
require 'bootstrap.php';
$start_tab = 'dashboard';
// $start_tab = 'configure';
// $start_tab = 'createquote';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/mockup/bootstrap.min.css" rel="stylesheet">
    <link href="/mockup/style.css" rel="stylesheet">
    <title>CPQ Tool</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>

<body>
    <header class="gradient">
        <div class="header-buttons">
            <div class="header-search input-group">
                <input type="text" class="form-control" placeholder="Type to search">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="button""><i class=" fa fa-search"></i></button>
                </div>
            </div>

            <a class="btn-new btn btn-ghost text-white btn-create-quote" href="#"><i class="fa-solid fa-plus"></i>
                NEW</a>
        </div>
    </header>

    <div id="cpq">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <!-- <li class="nav&#45;item"> -->
            <!--     <a class="nav&#45;link active" id="signin&#45;tab" data&#45;toggle="tab" href="#signin" role="tab" aria&#45;controls="signin" aria&#45;selected="true">Sign In</a> -->
            <!-- </li> -->
            <li class="nav-item">
                <a class="nav-link <?= $start_tab == 'dashboard' ? 'active' : '' ?>" id="dashboard-tab"
                    data-toggle="tab" href="#dashboard" role="tab" aria-controls="dashboard"
                    aria-selected="false">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $start_tab == 'configure' ? 'active' : '' ?>" id="configure-tab"
                    data-toggle="tab" href="#configure" role="tab" aria-controls="configure"
                    aria-selected="false">Configure</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $start_tab == 'createquote' ? 'active' : '' ?>" id="createquote-tab"
                    data-toggle="tab" href="#createquote" role="tab" aria-controls="createquote"
                    aria-selected="false">Quote</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <!-- <div class="tab&#45;pane fade  show active" id="signin" role="tabpanel" aria&#45;labelledby="signin&#45;tab"> -->
            <!--     <form id="form_signin"> -->
            <!--         <div class="form&#45;group"> -->
            <!--             <label for="email">Email address</label> -->
            <!--             <input type="email" class="form&#45;control" id="email" aria&#45;describedby="emailHelp" placeholder="Enter email"> -->
            <!--         </div> -->
            <!--         <div class="form&#45;group"> -->
            <!--             <label for="password">Password</label> -->
            <!--             <input type="password" class="form&#45;control" id="password" placeholder="Password"> -->
            <!--         </div> -->
            <!--         <button type="button" class="btn btn&#45;primary btn&#45;sign&#45;in">Sign In</button> -->
            <!--         <div class="mt&#45;4"> -->
            <!--             <p><a href="#">Forgot password?</a></p> -->
            <!--         </div> -->
            <!--     </form> -->
            <!-- </div> -->
            <div class="tab-pane fade <?= $start_tab == 'dashboard' ? 'show active' : '' ?>" id="dashboard"
                role="tabpanel" aria-labelledby="dashboard-tab">

                <div class="summary d-flex justify-content-between mb-3">
                    <div class="inner flex-grow-1 mr-3">
                        <h2 class="mb-3">Quote Summary</h2>
                        <div class="d-flex">
                            <div>
                                <h3>Signed</h3>
                                <p class="mb-0">2 / £80,000</p>
                            </div>
                            <div>
                                <h3>Awaiting Signature</h3>
                                <p class="mb-0">1 / £55,000</p>
                            </div>
                            <div>
                                <h3>Proposed</h3>
                                <p class="mb-0">1 / £25,000</p>
                            </div>
                        </div>
                    </div>
                    <div class="inner flex-grow-1">
                        <h2 class="mb-3">Supplier Summary</h2>
                        <div class="d-flex">
                            <div>
                                <h3>Own Service</h3>
                                <p class="mb-0">40%</p>
                            </div>
                            <div>
                                <h3>Giacom</h3>
                                <p class="mb-0">30%</p>
                            </div>
                            <div>
                                <h3>Other Provider</h3>
                                <p class="mb-0">30%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                    </div>
                    <div class="col">
                        <p class="text-right"><button class="btn btn-secondary dashboard-col-toggle"
                                type="button">Show/Hide Deal Info</button></p>


                    </div>
                </div>

                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Created</th>
                            <th scope="col">Quote #</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Solution</th>
                            <th scope="col">Total One-off (£)</th>
                            <th scope="col">Total Recurring (£)</th>
                            <th scope="col" class="dashboard-col-hidden">Margin (%)</th>
                            <th scope="col" class="dashboard-col-hidden">Commission (£)</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row">07/08/2023</th>
                            <td>Q12345</td>
                            <td>Acme Corp.</td>
                            <td>HVS</td>
                            <td class="text-right">25,000</td>
                            <td class="text-right">2,000</td>
                            <td class="dashboard-col-hidden text-right">35%</td>
                            <td class="dashboard-col-hidden text-right">2,625</td>
                            <td><span class="badge badge-info">Proposed</span></td>
                            <td><button type="button"
                                    class="btn btn-outline-primary btn-sm btn-cpq-edit-quote">EDIT</button> <button
                                    type="button" class="btn btn-outline-secondary btn-sm mr-1">ARCHIVE</button><button
                                    type="button"
                                    class="btn btn-outline btn-sm btn-outline-secondary">REVISIONS</button></td>
                        </tr>
                        <tr>
                            <th scope="row">06/08/2023</th>
                            <td>Q12346</td>
                            <td>Beta Ltd.</td>
                            <td>Cloud</td>
                            <td class="text-right">55,000</td>
                            <td class="text-right">5,000</td>
                            <td class="dashboard-col-hidden text-right">20%</td>
                            <td class="dashboard-col-hidden text-right">2,200</td>
                            <td><span class="badge badge-warning">Waiting for signature</span></td>
                            <td><button type="button"
                                    class="btn btn-outline-primary btn-sm btn-cpq-edit-quote">EDIT</button> <button
                                    type="button" class="btn btn-outline-secondary btn-sm mr-1">ARCHIVE</button><button
                                    type="button"
                                    class="btn btn-outline btn-sm btn-outline-secondary">REVISIONS</button></td>
                        </tr>
                        <tr>
                            <th scope="row">04/08/2023</th>
                            <td>Q12347</td>
                            <td>Delta Inc.</td>
                            <td>Connectivity</td>
                            <td class="text-right">30,000</td>
                            <td class="text-right">3,000</td>
                            <td class="dashboard-col-hidden text-right">40%</td>
                            <td class="dashboard-col-hidden text-right">3,600</td>
                            <td><span class="badge badge-success">Signed</span></td>
                            <td><button type="button"
                                    class="btn btn-outline-primary btn-sm btn-cpq-edit-quote">EDIT</button> <button
                                    type="button" class="btn btn-outline-secondary btn-sm mr-1">ARCHIVE</button><button
                                    type="button"
                                    class="btn btn-outline btn-sm btn-outline-secondary">REVISIONS</button></td>
                        </tr>
                        <tr>
                            <th scope="row">04/08/2023</th>
                            <td>Q12348</td>
                            <td>Epsilon Plc.</td>
                            <td>Hardware</td>
                            <td class="text-right">40,000</td>
                            <td class="text-right">4,000</td>
                            <td class="dashboard-col-hidden text-right">25%</td>
                            <td class="dashboard-col-hidden text-right">2,500</td>
                            <td><span class="badge badge-danger">Draft</span></td>
                            <td><button type="button"
                                    class="btn btn-outline-primary btn-sm btn-cpq-edit-quote">EDIT</button> <button
                                    type="button" class="btn btn-outline-secondary btn-sm mr-1">ARCHIVE</button><button
                                    type="button"
                                    class="btn btn-outline btn-sm btn-outline-secondary">REVISIONS</button></td>
                        </tr>
                        <tr>
                            <th scope="row">03/08/2023</th>
                            <td>Q12349</td>
                            <td>Zeta GmbH.</td>
                            <td>Mobile</td>
                            <td class="text-right">50,000</td>
                            <td class="text-right">5,000</td>
                            <td class="dashboard-col-hidden text-right">30%</td>
                            <td class="dashboard-col-hidden text-right">4,500</td>
                            <td><span class="badge badge-success">Signed</span></td>
                            <td><button type="button"
                                    class="btn btn-outline-primary btn-sm btn-cpq-edit-quote">EDIT</button> <button
                                    type="button" class="btn btn-outline-secondary btn-sm mr-1">ARCHIVE</button><button
                                    type="button"
                                    class="btn btn-outline btn-sm btn-outline-secondary">REVISIONS</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade <?= $start_tab == 'configure' ? 'show active' : '' ?>" id="configure"
                role="tabpanel" aria-labelledby="configure-tab">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer">Customer</label>
                            <select class="form-control" id="customer">
                                <option value="0">Find customer</option>
                                <option>Acme Corp.</option>
                                <option>Beta Ltd.</option>
                                <option>Delta Inc.</option>
                            </select>
                        </div>

                        <div class="form-group cpq-hidden-without-customer">
                            <label for="solutionType">Solution Type</label>
                            <select class="form-control" id="solutionType">
                                <option value="0">Select solution</option>
                                <option>Connectivity - Ethernet</option>
                                <option>Connectivity - Ethernet Point to Point</option>
                                <option>Connectivity - Fibre</option>

                                <!-- <option>Azure</option>
                                <option>Cloud License</option>
                                <option>Connectivity</option>
                                <option>HV.Select Supercharged</option>
                                <option>HVS Foundation</option>
                                <option>Mobile</option> -->
                            </select>
                        </div>

                        <hr>
                    </div>
                    <div class="col-md-6">
                    </div>
                </div>

                <form class="cpq-hidden-without-solution" id="configure_connectivity">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="configure-connectivity">
                                <h5 class="mb-3">Select product</h5>
                                <div class="form-group w-75">
                                    <label>Enter postcode</label>

                                    <div class="input-group">
                                        <input class="form-control" type="text" name="postcode" placeholder="Type to search">
                                        <div class="input-group-append">
                                            <button id="btn_postcode_lookup" class="btn btn-primary" type="button"><i class="fa fa-search"></i> Search available products</button>
                                        </div>
                                    </div>
                                </div>

                                <table id="table_products" class="table table-bordered table-striped table-hover" style="display:none">
                                    <thead>
                                        <tr>
                                            <th>Add to Quote</th>
                                            <th>Provider</th>
                                            <th>Product</th>
                                            <th>Speed</th>
                                            <th>Monthly</th>
                                            <th>Annual</th>
                                            <th>Set Up</th>
                                            <th>Contract Term</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr data-provider="loading">
                                            <td colspan="8"><i class="fa fa-refresh fa-spin"></i> Please wait...</td>
                                        </tr>
                                        <tr data-provider="giacom" style="display:none">
                                            <td class="text-center"><input type="checkbox" name="providers[]" value="giacom" required></td>
                                            <td class="text-center"><img class="img-provider" src="/mockup/images/providers/giacom.png" alt="Giacom"></td>
                                            <td>Ethernet</td>
                                            <td class="provider-speed">100 Mbps / 1 Gbps</td>
                                            <td class="text-right provider-monthly">£265.45</td>
                                            <td class="text-right provider-annual">£3,185.40</td>
                                            <td class="text-right provider-setup">£99.00</td>
                                            <td>36 months</td>
                                        </tr>

                                        <tr data-provider="gamma" style="display:none">
                                            <td class="text-center"><input type="checkbox" name="providers[]" value="gamma"></td>
                                            <td class="text-center"><img class="img-provider" src="/mockup/images/providers/gamma.png" alt="Gamma"></td>
                                            <td>Ethernet</td>
                                            <td class="provider-speed">100 Mbps / 1 Gbps</td>
                                            <td class="text-right provider-monthly">£289.23</td>
                                            <td class="text-right provider-annual">£3,470.76</td>
                                            <td class="text-right provider-setup">£99.00</td>
                                            <td>36 months</td>
                                        </tr>
                                        <tr data-provider="virtual1" style="display:none">
                                            <td class="text-center"><input type="checkbox" name="providers[]" value="virtual1"></td>
                                            <td class="text-center"><img class="img-provider" src="/mockup/images/providers/virtual1.png" alt="Virtual1"></td>
                                            <td>Ethernet</td>
                                            <td class="provider-speed">100 Mbps / 1 Gbps</td>
                                            <td class="text-right provider-monthly">£290.45</td>
                                            <td class="text-right provider-annual">£3,485.40</td>
                                            <td class="text-right provider-setup">£199.00</td>
                                            <td>36 months</td>
                                        </tr>
                                        <tr data-provider="its" style="display:none">
                                            <td class="text-center"><input type="checkbox" name="providers[]" value="its"></td>
                                            <td class="text-center"><img class="img-provider" src="/mockup/images/providers/its.png" alt="ITS"></td>
                                            <td>Ethernet</td>
                                            <td class="provider-speed">100 Mbps / 1 Gbps</td>
                                            <td class="text-right provider-monthly">£305.78</td>
                                            <td class="text-right provider-annual">£3,669.36</td>
                                            <td class="text-right provider-setup">£199.00</td>
                                            <td>36 months</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center my-3">
                                <button type="button" class="btn btn-primary" id="btn_create_quote" style="display:none">Create Quote</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div id="resources" class="gradient text-white">
                                <h4>Resources</h4>
                                <div><a href="#"><i class="fa-solid fa-play mr-1"></i> Solution Video</a></div>
                                <div><a href="#">Benefits Summary</a></div>
                                <div><a href="#">Product Brief</a></div>
                                <div><a href="#">Sales Battlecard</a></div>
                                <div><a href="#">Positioning</a></div>
                                <div><a href="#">Promotions</a></div>
                            </div>
                        </div>
                    </div>
                </form>

                <form id="configure_hv" style="display:none">
                    <div class="row">
                        <div class="col-md-4">

                            <div class="configure-category">
                                <h5 class="mb-3">Users</h5>
                                <div class="form-group">
                                    <label for="basicUsers">Basic Users</label>
                                    <input type="range" class="custom-range" id="basicUsers" min="0" max="250"
                                        value="0">
                                    <input type="number" class="form-control" id="basicUsersOutput" min="0" max="250"
                                        value="0">
                                </div>
                                <div class="form-group">
                                    <label for="standardUsers">Functional Users</label>
                                    <input type="range" class="custom-range" id="standardUsers" min="0" max="250"
                                        value="0">
                                    <input type="number" class="form-control" id="standardUsersOutput" min="0" max="250"
                                        value="0">
                                </div>
                                <div class="form-group">
                                    <label for="premiumUsers">Fixed Users</label>
                                    <input type="range" class="custom-range" id="premiumUsers" min="0" max="250"
                                        value="0">
                                    <input type="number" class="form-control" id="premiumUsersOutput" min="0" max="250"
                                        value="0">
                                </div>
                                <div class="form-group">
                                    <label for="premiumUsers">Mobile Users</label>
                                    <input type="range" class="custom-range" id="mobileUsers" min="0" max="250"
                                        value="0">
                                    <input type="number" class="form-control" id="mobileUsersOutput" min="0" max="250"
                                        value="0">
                                </div>
                            </div>
                            <div class="configure-category">
                                <h5 class="mb-3">Features</h5>
                                <div class="form-group">
                                    <label for="callRecording">Call Recording</label>
                                    <input type="range" class="custom-range" id="callRecording" min="0" max="250"
                                        value="0">
                                    <input type="number" class="form-control" id="callRecordingOutput" min="0" max="250"
                                        value="0">
                                </div>
                                <div class="form-group">
                                    <label for="callRecording">Voicemail</label>
                                    <input type="range" class="custom-range" id="voiceMail" min="0" max="250" value="0">
                                    <input type="number" class="form-control" id="voiceMailOutput" min="0" max="250"
                                        value="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="configure-category">
                                <h5 class="mb-3">Hardware</h5>
                                <div class="form-group">
                                    <label for="businessDeskPhone">Business Desk Phone</label>
                                    <input type="range" class="custom-range" id="businessDeskPhone" min="0" max="250"
                                        value="0">
                                    <input type="number" class="form-control" id="businessDeskPhoneOutput" min="0"
                                        max="250" value="0">
                                </div>
                                <div class="ecom" data-category="basic"></div>
                                <div class="form-group">
                                    <label for="premiumDeskPhone">Premium Desk Phone</label>
                                    <input type="range" class="custom-range" id="premiumDeskPhone" min="0" max="250"
                                        value="0">
                                    <input type="number" class="form-control" id="premiumDeskPhoneOutput" min="0"
                                        max="250" value="0">
                                </div>
                                <div class="ecom" data-category="premium"></div>
                                <div class="form-group">
                                    <label for="cordlessPhone">Cordless Phone</label>
                                    <input type="range" class="custom-range" id="cordlessPhone" min="0" max="250"
                                        value="0">
                                    <input type="number" class="form-control" id="cordlessPhoneOutput" min="0" max="250"
                                        value="0">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div id="resources" class="gradient text-white">
                                <h4>Resources</h4>
                                <div><a href="#"><i class="fa-solid fa-play mr-1"></i> Solution Video</a></div>
                                <div><a href="#">Benefits Summary</a></div>
                                <div><a href="#">Product Brief</a></div>
                                <div><a href="#">Sales Battlecard</a></div>
                                <div><a href="#">Positioning</a></div>
                                <div><a href="#">Promotions</a></div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center my-3">
                        <button type="button" class="btn btn-primary" id="btn_create_quote">Create Quote</button>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade <?= $start_tab == 'createquote' ? 'show active' : '' ?>" id="createquote"
                role="tabpanel" aria-labelledby="createquote-tab">
                <form>
                    <div class="row mb-5">
                        <div class="col-4">
                            <h2>Quote #12871</h2>
                        </div>
                        <div class="col-8">
                            <div class="text-right">
                                <button type="submit" class="btn btn-secondary">Save Draft</button>
                                <button type="submit" class="btn btn-outline-secondary">Save & Send for Approval</button>
                                <button type="button" class="btn btn-outline-secondary">Generate PDF</button>
                                <button type="button" class="btn btn-outline-secondary">Share</button>
                                <button class="btn btn-outline-secondary" type="button" data-toggle="collapse"
                                    data-target="#dealInfo" aria-expanded="false" aria-controls="dealInfo">Show/Hide
                                    Deal Info</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Find & Select Customer -->
                            <div class="form-group">
                                <label for="customer">Customer</label>
                                <select class="form-control" id="quoteCustomer">
                                    <option value="0">Find customer</option>
                                    <option>Acme Corp.</option>
                                    <option>Beta Ltd.</option>
                                    <option>Delta Inc.</option>
                                </select>
                            </div>

                            <!-- Configure Solution Type -->
                            <div class="form-group cpq-hidden-without-customer">
                                <label for="solutionType">Solution Type</label>
                                <select class="form-control" id="quoteSolutionType">
                                    <option value="0">Select solution</option>
                                    <option>Connectivity - Ethernet</option>
                                    <option>Connectivity - Ethernet Point to Point</option>
                                    <option>Connectivity - Fibre</option>
                                    <!-- <option>Azure</option>
                                    <option>Cloud License</option>
                                    <option>Connectivity</option>
                                    <option>HV.Select Supercharged</option>
                                    <option>HVS Foundation</option>
                                    <option>Mobile</option> -->
                                </select>
                            </div>

                        </div>
                        <div class="col-md-6 cpq-hidden-without-solution">
                            <div class="row">
                                <div class="col">
                                    <!-- Global Discount -->
                                    <div class="form-group">
                                        <label for="globalDiscount">Global Discount (%)</label>
                                        <input type="number" class="form-control" id="globalDiscount" value="0">
                                        <div><small><a href="#" data-toggle="modal"
                                                    data-target="#calculateDiscountModal"
                                                    id="calculate_discount">Calculate discount for budget</a></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <!-- Subscription Term -->
                                    <div class="form-group">
                                        <label for="subscriptionTerm">Subscription Term (months)</label>
                                        <input type="number" class="form-control" id="subscriptionTerm" value="36">
                                    </div>
                                </div>
                            </div>
                            <div class="collapse text-left" id="dealInfo">
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="total">Margin</label>
                                            <input type="number" class="form-control" id="total" value="0" readonly>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <div class="form-group">
                                            <label for="commission">Commission</label>
                                            <input type="number" class="form-control" id="commission" value="0"
                                                readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="summary d-flex justify-content-between mb-3">
                        <div class="inner flex-grow-1 mr-3">
                            <h2 class="mb-3">Cost Summary</h2>
                            <div class="d-flex">
                                <div>
                                    <h3>Total one-off cost to you</h3>
                                    <p class="mb-0">£6,600</p>
                                </div>
                                <div>
                                    <h3>Total one-off cost to customer</h3>
                                    <p class="mb-0">£7,500</p>
                                </div>
                                <div>
                                    <h3>Total monthly cost to you</h3>
                                    <p class="mb-0">£645</p>
                                </div>
                                <div>
                                    <h3>Total monthly cost to customer</h3>
                                    <p class="mb-0">£750</p>
                                </div>
                            </div>
                        </div>
                        <div class="inner flex-grow-1">
                            <h2 class="mb-3">Profitability</h2>
                            <div class="d-flex">
                                <div>
                                    <h3>Total one-off profit</h3>
                                    <p class="mb-0">£900</p>
                                </div>
                                <div>
                                    <h3>Total monthly profit</h3>
                                    <p class="mb-0">£105</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="cpq-products mb-3 cpq-hidden-without-solution">
                    
                        <!-- Product List -->
                        <!-- In a real application, this would be dynamically generated based on the selected solution type -->
                        <table class="table table-hover">
                            <!-- Headers -->
                            <tr>
                                <th></th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Description</th>
                                <th>Line Discount</th>
                            </tr>
                            <?php foreach ($products as $category => $category_products): ?>
                            <tr style="display:none">
                                <td colspan="8"><b>
                                        <?= ucwords($category) ?>
                                    </b></td>
                            </tr>
                            <?php foreach ($category_products as $product): ?>
                            <tr style="display:none">
                                <td style="vertical-align:middle">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox">
                                    </div>
                                </td>
                                <td><img src="<?= $product['image'] ?? '/mockup/images/product.png' ?>" width="50" height="50">
                                </td>
                                <td>
                                    <?= $product['name'] ?>
                                </td>
                                <td>
                                    <?= $product['type'] ?>
                                </td>
                                <td>
                                    <div class="cpq-qty d-flex">
                                        <input type="number" data-name="<?= slugify($product['name']) ?>"
                                            class="form-control input-qty mr-1" value="0">
                                        <button class="btn btn-secondary btn-plus mr-1" type="button"><i
                                                class="fa-solid fa-plus"></i></button>
                                        <button class="btn btn-secondary btn-minus" type="button"><i
                                                class="fa-solid fa-minus"></i></button>
                                    </div>
                                </td>
                                <td>
                                    <?= $product['price'] ?? '' ?>
                                </td>
                                <td>
                                    <?= $product['description'] ?? 'Example product description' ?>
                                </td>
                                <td><input type="number" class="form-control" id="discount1" value="0"></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endforeach; ?>
                        </table>

                        <div class="cpq-total text-right">
                            <div class="row">
                                <div class="col"></div>
                                <div class="col">
                                    <table class="table table-hover">
                                        <tr>
                                            <th></th>
                                            <th>NET</th>
                                            <th>VAT (20%)</th>
                                            <th>GROSS</th>
                                        </tr>
                                        <tr>
                                            <th>Total Monthly</th>
                                            <td>£1,000.00</td>
                                            <td>£200.00</td>
                                            <td>£1,200.00</td>
                                        </tr>
                                        <tr>
                                            <th>Monthly (per seat)</th>
                                            <td>£100.00</td>
                                            <td>£20.00</td>
                                            <td>£120.00</td>
                                        </tr>
                                        <tr>
                                            <th>Total One-off Costs</th>
                                            <td>£10,000.00</td>
                                            <td>£2,000.00</td>
                                            <td>£12,000.00</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="calculateDiscountModal" tabindex="-1" role="dialog"
        aria-labelledby="calculateDiscountModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Calculate Discount</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label>Target Budget</label>
                            <input class="form-control" type="number" name="budget">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Calculate Discount</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/mockup/jquery-3.5.1.min.js"></script>
    <script src="/mockup/bootstrap.bundle.min.js"></script>
    <script>

        var products = {
            "phones": {
                "basic": [
                    {
                        "name": "Yealink T31G",
                        "description": "Yealink T31G VoIP/SIP Phone, 2-Lines, 2 x Gigabit Ports, PoE, 2.3-inch Greyscale LCD Display w/ PSU",
                        "image": "t31p-img1.webp"
                    },
                    {
                        "name": "Polycom VVX 150",
                        "description": "Polycom VVX 150 Desktop IP phone with 2 SIP lines",
                        "image": "vvx150_2.jpg"
                    },
                    {
                        "name": "Cisco Softphone",
                        "description": "Software-based phone",
                        "image": "softphone.png"
                    },
                ],
                "premium": [
                    {


                        "name": "Poly CCX 700",
                        "description": "Poly CCX 700 WiFi VoIP/SIP Video Phone, 2 x Gigabit Ports, PoE+, 7-inch Colour LCD Touchscreen Display w/ Handset",
                        "image": "44546-img1.png"
                    },
                    {
                        "name": "Cisco IP 8845",
                        "description": "Cisco IP Video Phone 8845 shipped with multiplatform phone firmware",
                        "image": "cisco-8845-65-ip-phone-vl_1.webp"
                    }
                ],
            }
        };


        $(document).ready(function () {
            $(".dashboard-col-toggle").on("click", function (e) {
                e.preventDefault();
                $(".dashboard-col-hidden").toggle();
            });

            $(".btn-sign-in").on("click", function () {
                $("#dashboard-tab").click();
            });

            $(".btn-cpq-edit-quote, .btn-create-quote").on("click", function () {
                $("#configure-tab").click();
            });

            $("#btn_create_quote").on("click", function (e) {
                e.preventDefault();

                if ($('#table_products input:checkbox:checked').length > 0) {
                    $("#createquote-tab").click();

                    var providers = $("#table_products input:checkbox:checked").map(function() {
                        return $(this).val();
                    }).get();

                    providers.forEach(provider => {
                        let provider_row = $("tr[data-provider=" + provider + "]");
                        let name = provider_row.find(".img-provider").attr("alt");
                        let speed = provider_row.find(".provider-speed").text();
                        let product = provider_row.find(".provider-monthly").text();
                        let monthly = provider_row.find(".provider-monthly").text();

                        let tbl_row = `
                        <tr>
                            <td style="vertical-align:middle">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox">
                                </div>
                            </td>
                            <td><img src="/mockup/images/product.png" width="50" height="50">
                            </td>
                            <td>${name}</td>
                            <td>Monthly Recurring</td>
                            <td>
                                <div class="cpq-qty d-flex">
                                    <input type="number" data-name="hv-select-sip-ddi-user" class="form-control input-qty mr-1" value="0">
                                    <button class="btn btn-secondary btn-plus mr-1" type="button"><i class="fa-solid fa-plus"></i></button>
                                    <button class="btn btn-secondary btn-minus" type="button"><i class="fa-solid fa-minus"></i></button>
                                </div>
                            </td>
                            <td>${monthly}</td>
                            <td>${speed}</td>
                            <td><input type="number" class="form-control" id="discount1" value="0"></td>
                        </tr>
                        `;

                        $(".cpq-products > table tbody").append(tbl_row);
                    });

                    // $("input[data-name='hv-select-sip-ddi-user']").val($("#basicUsersOutput").val());
                    // $("input[data-name='hv-select-sip-ddi-functional-user']").val($("#standardUsersOutput").val());
                    // $("input[data-name='hv-select-sip-ddi-fixed-user']").val($("#premiumUsersOutput").val());
                    // $("input[data-name='hv-select-sip-ddi-mobile-user']").val($("#mobileUsersOutput").val());
                } else {
                    alert("Please tick at least one product");
                }
            });

            $("select#customer, select#solutionType").on("change", function () {
                var customer = $("select#customer").val();
                var solutionType = $("select#solutionType").val();

                $("select#quoteCustomer").val(customer);
                $("select#quoteSolutionType").val(solutionType);

                if (customer != "0") {
                    $(".cpq-hidden-without-customer").show();
                } else {
                    $(".cpq-hidden-without-customer").hide();
                }

                if (customer != "0" && solutionType != "0") {
                    $(".cpq-hidden-without-solution").show();
                } else {
                    $(".cpq-hidden-without-solution").hide();
                }
            });

            $(".cpq-qty .btn-plus").on("click", function () {
                var i = $(this).parent().find("input[type=number]");
                var q = parseInt(i.val());
                q++;
                i.val(q);
            });

            $(".cpq-qty .btn-minus").on("click", function () {
                var i = $(this).parent().find("input[type=number]");
                var q = parseInt(i.val());
                q--;
                if (q < 0) {
                    q = 0;
                }
                i.val(q);
            });

            $("#btn_postcode_lookup").on("click", function() {
                $("#table_products").show();

                setTimeout(function() {
                    $("#table_products [data-provider=its]").show();
                }, 1000);

                setTimeout(function() {
                    $("#table_products [data-provider=giacom]").show();
                }, 2000);

                setTimeout(function() {
                    $("#table_products [data-provider=virtual1]").show();
                }, 3000);

                setTimeout(function() {
                    $("#table_products [data-provider=gamma]").show();
                    $("#table_products [data-provider=loading]").hide();
                    $("#btn_create_quote").show();
                }, 4000);
            });
        });

        const rangeIds = ['basicUsers', 'standardUsers', 'premiumUsers', 'mobileUsers', 'callRecording', 'voiceMail', 'businessDeskPhone', 'premiumDeskPhone', 'cordlessPhone'];

        rangeIds.forEach(id => {
            const rangeInput = document.getElementById(id);
            const outputInput = document.getElementById(`${id}Output`);

            rangeInput.addEventListener('input', () => {
                outputInput.value = rangeInput.value;
            });

            outputInput.addEventListener('input', () => {
                rangeInput.value = outputInput.value;
            });
        });

        document.querySelectorAll(".ecom").forEach(el => {
            if (typeof products.phones[el.dataset.category] != "undefined") {
                let html = '';
                products.phones[el.dataset.category].forEach(phone => {
                    html += `
                            <div class="ecom-product">
                            <img class="ecom-product-image" src="/mockup/images/products/${phone.image}">
                            <div class="ecom-product-name" title="${phone.description}">${phone.name}</div>
                            </div>
                            `;
                });

                el.innerHTML = html;
            }

            initEcomEvents();
        });

        function initEcomEvents() {
            document.querySelectorAll(".ecom-product").forEach(el => {
                el.addEventListener("mousedown", () => {
                    el.parentNode.querySelectorAll(".ecom-product").forEach(el => {
                        el.style.border = '1px solid #eee';
                    });
                    el.style.border = '2px solid #000';
                });
            });
        }
    </script>
</body>

</html>