<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\ProductCategoryModel;
use App\Models\UnitModel;
use CodeIgniter\HTTP\ResponseInterface;

class ProductController extends BaseController
{
    protected $productModel;
    protected $categoryModel;
    protected $unitModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new ProductCategoryModel();
        $this->unitModel = new UnitModel();
        helper(['form', 'url']);
    }

    /**
     * List all products
     */
    public function index()
    {
        $data = [
            'title' => 'Products',
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Products']
            ]
        ];

        return view('master/product/index', $data);
    }

    /**
     * DataTables server-side processing
     */
    public function datatable()
    {
        $request = service('request');
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $companyId = getCurrentCompanyId();

        // Base query with joins
        $builder = $this->productModel->builder();
        $builder->select('products.*, product_categories.name as category_name, units.name as unit_name')
            ->join('product_categories', 'product_categories.id = products.category_id', 'left')
            ->join('units', 'units.id = products.unit_id', 'left')
            ->where('products.company_id', $companyId)
            ->where('products.deleted_at IS NULL');

        // Search
        if ($searchValue) {
            $builder->groupStart()
                ->like('products.sku', $searchValue)
                ->orLike('products.name', $searchValue)
                ->groupEnd();
        }

        // Total records
        $totalFiltered = $builder->countAllResults(false);

        // Get data
        $products = $builder->orderBy('products.created_at', 'DESC')
            ->limit($length, $start)
            ->get()
            ->getResultArray();

        // Format data for DataTables
        $data = [];
        foreach ($products as $product) {
            $statusBadge = $product['status'] == 'active' 
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';

            $image = $product['image'] 
                ? '<img src="' . base_url('uploads/products/' . $product['image']) . '" width="40" class="img-thumbnail">'
                : '<span class="badge badge-secondary">No Image</span>';

            $actions = '
                <div class="btn-group">
                    <a href="' . base_url('master/product/edit/' . $product['id']) . '" class="btn btn-sm btn-primary" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' . $product['id'] . '" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            ';

            $data[] = [
                'sku' => esc($product['sku']),
                'image' => $image,
                'name' => esc($product['name']),
                'category' => esc($product['category_name'] ?? '-'),
                'unit' => esc($product['unit_name'] ?? '-'),
                'price' => number_format($product['price'], 2),
                'stock_alert' => $product['stock_alert_level'],
                'status' => $statusBadge,
                'actions' => $actions
            ];
        }

        // Total records without filter
        $totalRecords = $this->productModel->where('company_id', $companyId)->countAllResults();

        return $this->response->setJSON([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $data = [
            'title' => 'Create Product',
            'categories' => $this->categoryModel->getByCompany(),
            'units' => $this->unitModel->getByCompany(),
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Products', 'url' => base_url('master/product')],
                ['label' => 'Create']
            ]
        ];

        return view('master/product/create', $data);
    }

    /**
     * Save new product
     */
    public function store()
    {
        $rules = [
            'sku' => 'required|max_length[100]',
            'name' => 'required|min_length[3]|max_length[255]',
            'price' => 'required|decimal',
            'cost' => 'permit_empty|decimal',
            'image' => 'permit_empty|max_size[image,2048]|is_image[image]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'company_id' => getCurrentCompanyId(),
            'sku' => $this->request->getPost('sku'),
            'name' => $this->request->getPost('name'),
            'category_id' => $this->request->getPost('category_id') ?: null,
            'unit_id' => $this->request->getPost('unit_id') ?: null,
            'price' => $this->request->getPost('price'),
            'cost' => $this->request->getPost('cost') ?: 0,
            'stock_alert_level' => $this->request->getPost('stock_alert_level') ?: 0,
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status') ?? 'active',
            'created_by' => getCurrentUserId()
        ];

        // Handle image upload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(WRITEPATH . '../public/uploads/products', $newName);
            $data['image'] = $newName;
        }

        if ($this->productModel->insert($data)) {
            logActivity('create', 'products', "Created product: {$data['name']}", $this->productModel->getInsertID());
            return redirect()->to('master/product')->with('success', 'Product created successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create product');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $product = $this->productModel->where('company_id', getCurrentCompanyId())->find($id);

        if (!$product) {
            return redirect()->to('master/product')->with('error', 'Product not found');
        }

        $data = [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $this->categoryModel->getByCompany(),
            'units' => $this->unitModel->getByCompany(),
            'breadcrumbs' => [
                ['label' => 'Master Data', 'url' => '#'],
                ['label' => 'Products', 'url' => base_url('master/product')],
                ['label' => 'Edit']
            ]
        ];

        return view('master/product/edit', $data);
    }

    /**
     * Update product
     */
    public function update($id)
    {
        $product = $this->productModel->where('company_id', getCurrentCompanyId())->find($id);

        if (!$product) {
            return redirect()->to('master/product')->with('error', 'Product not found');
        }

        $rules = [
            'sku' => 'required|max_length[100]',
            'name' => 'required|min_length[3]|max_length[255]',
            'price' => 'required|decimal',
            'cost' => 'permit_empty|decimal',
            'image' => 'permit_empty|max_size[image,2048]|is_image[image]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'sku' => $this->request->getPost('sku'),
            'name' => $this->request->getPost('name'),
            'category_id' => $this->request->getPost('category_id') ?: null,
            'unit_id' => $this->request->getPost('unit_id') ?: null,
            'price' => $this->request->getPost('price'),
            'cost' => $this->request->getPost('cost') ?: 0,
            'stock_alert_level' => $this->request->getPost('stock_alert_level') ?: 0,
            'description' => $this->request->getPost('description'),
            'status' => $this->request->getPost('status') ?? 'active',
            'updated_by' => getCurrentUserId()
        ];

        // Handle image upload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            // Delete old image
            if ($product['image'] && file_exists(WRITEPATH . '../public/uploads/products/' . $product['image'])) {
                unlink(WRITEPATH . '../public/uploads/products/' . $product['image']);
            }

            $newName = $image->getRandomName();
            $image->move(WRITEPATH . '../public/uploads/products', $newName);
            $data['image'] = $newName;
        }

        if ($this->productModel->update($id, $data)) {
            logActivity('update', 'products', "Updated product: {$data['name']}", $id);
            return redirect()->to('master/product')->with('success', 'Product updated successfully');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update product');
    }

    /**
     * Delete product
     */
    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $product = $this->productModel->where('company_id', getCurrentCompanyId())->find($id);

        if (!$product) {
            return $this->response->setJSON(['success' => false, 'message' => 'Product not found']);
        }

        // Soft delete
        if ($this->productModel->delete($id)) {
            logActivity('delete', 'products', "Deleted product: {$product['name']}", $id);
            return $this->response->setJSON(['success' => true, 'message' => 'Product deleted successfully']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete product']);
    }
}
