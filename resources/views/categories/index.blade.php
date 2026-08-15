<x-layouts.app>
    <x-slot:title>Categories</x-slot>

    <div x-data="categoriesPage()" x-init="load()" class="space-y-4">

        <div class="flex items-center justify-between">
            <div class="relative w-full max-w-xs">
                <input
                    type="text"
                    x-model="search"
                    placeholder="Search categories..."
                    class="w-full rounded-lg border-0 bg-white py-2 pl-9 pr-4 text-sm shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-600"
                />
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
                </svg>
            </div>
            <x-button @click="openCreate()">+ Add Category</x-button>
        </div>

        <x-table>
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Products</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="category in categories" :key="category.id">
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800" x-text="category.name"></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700" x-text="(category.products_count ?? 0) + ' products'"></span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <x-button size="sm" variant="secondary" @click="openEdit(category)">Edit</x-button>
                                <x-button size="sm" variant="danger" @click="confirmDelete(category)">Delete</x-button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </x-table>

        <div x-show="!loading && categories.length === 0" class="flex flex-col items-center gap-2 rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-400">
            <span class="text-4xl leading-none"><span class="material-icons" aria-hidden="true">category</span></span>
            <p class="text-sm">No categories yet.</p>
            <x-button size="sm" @click="openCreate()">+ Add your first category</x-button>
        </div>

        <x-modal show="formOpen" maxWidth="max-w-md">
            <form @submit.prevent="save()" class="p-5 sm:p-6">
                <h3 class="mb-4 text-lg font-semibold text-slate-900" x-text="editingId ? 'Edit Category' : 'Create Category'"></h3>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" x-model="form.name" class="block w-full rounded-lg border-0 px-3 py-2 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600" />
                    <p class="mt-1 text-xs text-red-600" x-show="errors.name" x-text="errors.name?.[0]"></p>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-button type="button" variant="secondary" @click="formOpen = false">Cancel</x-button>
                    <x-button type="submit" x-bind:disabled="saving" x-bind:class="{ 'is-loading': saving }" x-text="editingId ? 'Save Changes' : 'Create Category'">Save</x-button>
                </div>
            </form>
        </x-modal>

        <x-modal show="deleteOpen" maxWidth="max-w-md">
            <div class="p-5 sm:p-6">
                <h3 class="mb-2 text-lg font-semibold text-slate-900">Delete Category</h3>
                <p class="mb-5 text-sm text-slate-500">
                    Are you sure you want to delete <strong class="text-slate-700" x-text="deleteTarget?.name"></strong>?
                    Categories with existing products cannot be deleted.
                </p>
                <div class="flex justify-end gap-3">
                    <x-button variant="secondary" @click="deleteOpen = false">Cancel</x-button>
                    <x-button variant="danger" x-bind:disabled="deleting" x-bind:class="{ 'is-loading': deleting }" @click="destroy()">Delete</x-button>
                </div>
            </div>
        </x-modal>
    </div>

    <script>
        function categoriesPage() {
            return {
                categories: [],
                search: '',
                loading: true,
                saving: false,
                deleting: false,
                formOpen: false,
                deleteOpen: false,
                editingId: null,
                deleteTarget: null,
                errors: {},
                form: { name: '' },

                async load() {
                    this.loading = true;
                    try {
                        const params = { with_count: true };
                        if (this.search) params.search = this.search;
                        const { data } = await axios.get('/api/categories', { params });
                        this.categories = data;
                    } catch {
                        this.$store.toast.error('Failed to load categories.');
                    } finally {
                        this.loading = false;
                    }
                },

                init() {
                    this.load();
                    this.$watch('search', () => this.load());
                },

                openCreate() {
                    this.editingId = null;
                    this.form = { name: '' };
                    this.errors = {};
                    this.formOpen = true;
                },

                openEdit(category) {
                    this.editingId = category.id;
                    this.form = { name: category.name };
                    this.errors = {};
                    this.formOpen = true;
                },

                async save() {
                    this.saving = true;
                    this.errors = {};
                    try {
                        if (this.editingId) {
                            await axios.put(`/api/categories/${this.editingId}`, this.form);
                            this.$store.toast.success('Category updated successfully.');
                        } else {
                            await axios.post('/api/categories', this.form);
                            this.$store.toast.success('Category created successfully.');
                        }
                        this.formOpen = false;
                        await this.load();
                    } catch (error) {
                        this.errors = error.response?.data?.errors ?? {};
                        this.$store.toast.error('Please fix the form errors.');
                    } finally {
                        this.saving = false;
                    }
                },

                confirmDelete(category) {
                    this.deleteTarget = category;
                    this.deleteOpen = true;
                },

                async destroy() {
                    this.deleting = true;
                    try {
                        await axios.delete(`/api/categories/${this.deleteTarget.id}`);
                        this.$store.toast.success('Category deleted successfully.');
                        this.deleteOpen = false;
                        await this.load();
                    } catch (error) {
                        this.$store.toast.error(error.response?.data?.message ?? 'Failed to delete category.');
                    } finally {
                        this.deleting = false;
                    }
                },
            };
        }
    </script>
</x-layouts.app>
