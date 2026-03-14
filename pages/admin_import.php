<div class="container mt-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="page-title">Data Import / Export</h2>
            <p class="text-muted">Bulk upload new dictionary entries or export existing ones.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Import Dictionary Data</h5>
                </div>
                <div class="card-body">
                    <form action="index.php?page=import" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label for="dictSelect" class="form-label fw-bold">Target Dictionary</label>
                            <select class="form-select" id="dictSelect" name="dictionary_id" required>
                                <option value="">-- Select a Dictionary --</option>
                                <option value="1">Telugu-English</option>
                                <option value="2">Sanskrit-English</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="fileUpload" class="form-label fw-bold">Upload File</label>
                            <input class="form-control" type="file" id="fileUpload" name="import_file" accept=".csv, .json, .xlsx" required>
                            <div class="form-text">Accepted formats: CSV, JSON, Excel.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Run Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>