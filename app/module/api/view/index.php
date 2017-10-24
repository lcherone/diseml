
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header" data-background-color="blue">
                <h4 class="title"><i class="fa fa-globe"></i> API</h4>
                <p class="category">Free and simple API endpoints to the data.</p>
            </div>
            <div class="card-block">
                <div class="card-content">
                    <h4>Dump</h4>
                    <table class="table table-sm mb-4" style="width:100%">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="width:50%">Endpoint</th>
                                <th style="width:15%">Format</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($endpoints as $row): ?>
                            <tr>
                                <td><?= $row['description'] ?></td>
                                <td><a href="<?= $row['endpoint'] ?>"><?= $row['endpoint'] ?></a></td>
                                <td><?= $row['format'] ?></td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                    <h4>Check</h4>
                    <table class="table table-sm mb-4" style="width:100%">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="width:50%">Endpoint</th>
                                <th style="width:15%">Format</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Single Domain</td>
                                <td><a href="/api/check/example.com">/api/check/example.com</a></td>
                                <td>JSON</td>
                            </tr>						  
                            <tr>
                                <td>Single Domain</td>
                                <td><a href="/api/check/example.com/json">/api/check/example.com/json</a></td>
                                <td>JSON</td>
                            </tr>		
                            <tr>
                                <td>Single Domain</td>
                                <td><a href="/api/check/example.com/txt">/api/check/example.com/txt</a></td>
                                <td>Text</td>
                            </tr>						    
                        </tbody>
                    </table>
                    <h4>Validate</h4>
                    <table class="table table-sm mb-4" style="width:100%">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="width:50%">Endpoint</th>
                                <th style="width:15%">Format</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Single Domain</td>
                                <td><a href="/api/validate/example.com">/api/validate/example.com</a></td>
                                <td>JSON</td>
                            </tr>						    
                            <tr>
                                <td>Single Domain (MX check)</td>
                                <td><a href="/api/validate/example.com">/api/validate/example.com?dns=mx</a></td>
                                <td>JSON</td>
                            </tr>						
                            <tr>
                                <td>Single Domain (A check)</td>
                                <td><a href="/api/validate/example.com">/api/validate/example.com?dns=a</a></td>
                                <td>JSON</td>
                            </tr>
                        </tbody>
                    </table>
                    <h4>Data</h4>
                    <table class="table table-sm mb-4" style="width:100%">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="width:50%">Endpoint</th>
                                <th style="width:15%">Format</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Single Domain</td>
                                <td><a href="/api/data/example.com">/api/data/example.com</a></td>
                                <td>JSON</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h4>Submit (Validate+Data)</h4>
                    <table class="table table-sm mb-4" style="width:100%">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="width:50%">Endpoint</th>
                                <th style="width:15%">Format</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Single Domain</td>
                                
                                <td><a href="/api/submit/example.com">/api/submit/example.com</a></td>
                                <td>JSON</td>
                            </tr>						    
                            <tr>
                                <td>Multiple Domains</td>
                                <td><a href="/api/submit?domain[]=example.com">/api/submit?domain[]=example.com</a></td>
                                <td>JSON</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>