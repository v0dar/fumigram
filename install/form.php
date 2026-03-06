<div class="installation" style="display: none;">
    <form id="install-form">
        <div class="message d-none">
            <div class="alert alert-danger" role="alert">
                <b>ERROR : </b>
                <span></span>
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="mb-2">How did you discover Fumigram?</label>
            <select class="form-control" name="discover">
                <option value="">-----------</option>
                <option value="Social media">Social Media</option>
                <option value="Search engine">Search Engine</option>
                <option value="Discord server">Discord Server</option>
                <option value="Referral">Referred by someone</option>
                <option value="Other ways">Others</option>
            </select>
        </div>

        <div class="form-group">
            <h5>Database Details</h5>
        </div>

        <div class="row mb-3">
            <div class="form-group col-md-6">
                <label class="mb-2">Hostname</label>
                <input type="text" name="sql_host" class="form-control" placeholder="Database Hostname" value="localhost" />
            </div>
            <div class="form-group col-md-6">
                <label class="mb-2">Database</label>
                <input type="text" name="sql_name" class="form-control" placeholder="Database Name" />
            </div>
        </div>

        <div class="row mb-3">
            <div class="form-group col-md-6">
                <label class="mb-2">Username</label>
                <input type="text" name="sql_user" class="form-control" placeholder="Database Username" />
            </div>
            <div class="form-group col-md-6">
                <label class="mb-2">Password</label>
                <input type="text" name="sql_pass" class="form-control" placeholder="Database Password" />
            </div>
        </div>

        <div class="form-group">
            <h5>App Details</h5>
        </div>

        <div class="row mb-3">
            <div class="form-group col-md-6">
                <label class="mb-2">Site Title</label>
                <input type="text" name="site_title" class="form-control" placeholder="Website Title" />
            </div>
            <div class="form-group col-md-6">
                <label class="mb-2">Site Email</label>
                <input type="text" name="site_email" class="form-control" placeholder="Website Email" />
            </div>
        </div>

        <div class="form-group mb-3">
            <label class="mb-2">Website URL</label>
            <input type="text" name="site_url" class="form-control" placeholder="Your Website URL" />
        </div>

        <div class="form-group">
            <h5>Account Details</h5>
            <p>Type in your email address. <br> Preferred username and password for your admin account!</p>
        </div>

        <div class="form-group mb-3">
            <label class="mb-2">Email Address</label>
            <input type="email" name="email_address" class="form-control" placeholder="Email Address" />
        </div>

        <div class="row mb-3">
            <div class="form-group col-md-6">
                <label class="mb-2">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Username" />
            </div>
            <div class="form-group col-md-6">
                <label class="mb-2">Password</label>
                <input type="text" name="password" class="form-control" placeholder="Password" />
            </div>
        </div>

        <div class="row mb-3">
        <div class="form-group col-md-6">
                <label class="mb-2">Gender</label>
                <select class="form-control" name="gender">
                    <option value="male" selected>Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label class="mb-2">Hexagon</label>
                <select class="form-control" name="hexagon">
                    <option value="1" selected>Activate</option>
                    <option value="0">Deactivate</option>
                </select>
            </div>
        </div>
        <input type="hidden" name="install" value="install" />
        <button type="submit" class="install">
            <span><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="margin-top:-6px"><path fill="currentColor" d="M14.46,23.12012a.9991.9991,0,0,1-.40723-.08692,9.004,9.004,0,0,1-4.3125-4.0166,6.977,6.977,0,0,0,.18995,2.58106,1.04987,1.04987,0,0,1,.0498.3125.99942.99942,0,0,1-1.37744.92578A8.98348,8.98348,0,0,1,4.94727,8.92773a8.51269,8.51269,0,0,1,1.9873-1.8623l.23047-.1875A7.017,7.017,0,0,0,9.63623,1.93555a1.00009,1.00009,0,0,1,1.49121-.80567,8.717,8.717,0,0,1,4.26709,9.0918,5.78155,5.78155,0,0,0,1.398-1.77734.99959.99959,0,0,1,1.39941-.41114,1.237,1.237,0,0,1,.23.17481,8.99474,8.99474,0,0,1-3.65381,14.86328A1.00594,1.00594,0,0,1,14.46,23.12012Zm-6.93017-15.25h0Z"></path></svg> Install</span>
        </button>
    </form>
</div>