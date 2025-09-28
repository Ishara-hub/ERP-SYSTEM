/**
 * Enhanced Account Creation Workflow Example
 * 
 * This example demonstrates how to create parent accounts and sub-accounts
 * with proper category/type selection and parent account selection.
 */

class AccountCreationManager {
    constructor() {
        this.apiBaseUrl = '/api';
        this.accountTypes = {
            'Asset': 'Assets',
            'Liability': 'Liabilities',
            'Equity': 'Equity',
            'Income': 'Income',
            'Expense': 'Expenses'
        };
    }

    /**
     * Step 1: Create Parent Account
     * User selects account category/type first
     */
    async createParentAccount(accountData) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/accounts`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify({
                    account_code: accountData.account_code,
                    account_name: accountData.account_name,
                    account_type: accountData.account_type, // Asset, Liability, etc.
                    parent_id: null, // Parent accounts have no parent
                    description: accountData.description,
                    opening_balance: accountData.opening_balance || 0,
                    is_active: true,
                    sort_order: accountData.sort_order || 0
                })
            });

            const result = await response.json();
            
            if (response.ok) {
                console.log('Parent account created successfully:', result.data);
                return result.data;
            } else {
                throw new Error(result.message || 'Failed to create parent account');
            }
        } catch (error) {
            console.error('Error creating parent account:', error);
            throw error;
        }
    }

    /**
     * Step 2: Get Parent Accounts for Sub-Account Creation
     * Load parent accounts based on selected account type
     */
    async getParentAccountsByType(accountType) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/accounts/parent-accounts-by-type?account_type=${accountType}`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`
                }
            });

            const result = await response.json();
            
            if (response.ok) {
                return result.data;
            } else {
                throw new Error(result.message || 'Failed to fetch parent accounts');
            }
        } catch (error) {
            console.error('Error fetching parent accounts:', error);
            throw error;
        }
    }

    /**
     * Step 3: Create Sub-Account
     * User selects account type, then selects from available parent accounts
     */
    async createSubAccount(accountData) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/accounts`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify({
                    account_code: accountData.account_code,
                    account_name: accountData.account_name,
                    account_type: accountData.account_type,
                    parent_id: accountData.parent_id, // Required for sub-accounts
                    description: accountData.description,
                    opening_balance: accountData.opening_balance || 0,
                    is_active: true,
                    sort_order: accountData.sort_order || 0
                })
            });

            const result = await response.json();
            
            if (response.ok) {
                console.log('Sub-account created successfully:', result.data);
                return result.data;
            } else {
                throw new Error(result.message || 'Failed to create sub-account');
            }
        } catch (error) {
            console.error('Error creating sub-account:', error);
            throw error;
        }
    }

    /**
     * Get all parent accounts grouped by type
     */
    async getAllParentAccounts() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/accounts/parent-accounts`, {
                headers: {
                    'Authorization': `Bearer ${this.getAuthToken()}`
                }
            });

            const result = await response.json();
            
            if (response.ok) {
                return result.data;
            } else {
                throw new Error(result.message || 'Failed to fetch parent accounts');
            }
        } catch (error) {
            console.error('Error fetching all parent accounts:', error);
            throw error;
        }
    }

    /**
     * Helper method to get authentication token
     */
    getAuthToken() {
        // Replace with your actual token retrieval logic
        return localStorage.getItem('auth_token') || sessionStorage.getItem('auth_token');
    }

    /**
     * Example workflow: Create complete account hierarchy
     */
    async demonstrateAccountCreation() {
        try {
            console.log('=== Account Creation Workflow Demo ===');

            // Step 1: Create a parent Asset account
            console.log('\n1. Creating parent Asset account...');
            const parentAccount = await this.createParentAccount({
                account_code: '1001',
                account_name: 'Current Assets',
                account_type: 'Asset',
                description: 'Current assets that can be converted to cash within one year'
            });

            // Step 2: Get parent accounts for Asset type
            console.log('\n2. Fetching parent accounts for Asset type...');
            const assetParents = await this.getParentAccountsByType('Asset');
            console.log('Available Asset parent accounts:', assetParents);

            // Step 3: Create sub-accounts under the parent
            console.log('\n3. Creating sub-accounts...');
            
            const cashAccount = await this.createSubAccount({
                account_code: '1001001',
                account_name: 'Cash in Hand',
                account_type: 'Asset',
                parent_id: parentAccount.id,
                description: 'Physical cash available'
            });

            const bankAccount = await this.createSubAccount({
                account_code: '1001002',
                account_name: 'Bank Account',
                account_type: 'Asset',
                parent_id: parentAccount.id,
                description: 'Money in bank accounts'
            });

            console.log('\n=== Account Creation Complete ===');
            console.log('Parent Account:', parentAccount);
            console.log('Sub-accounts:', [cashAccount, bankAccount]);

        } catch (error) {
            console.error('Demo failed:', error);
        }
    }
}

// Usage example
const accountManager = new AccountCreationManager();

// Example: Create accounts programmatically
// accountManager.demonstrateAccountCreation();

// Example: Get parent accounts for a dropdown
// accountManager.getParentAccountsByType('Asset').then(parents => {
//     console.log('Asset parent accounts for dropdown:', parents);
// });

/**
 * Frontend Form Integration Example
 */
class AccountFormHandler {
    constructor() {
        this.accountManager = new AccountCreationManager();
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        // Account type change handler
        const accountTypeSelect = document.getElementById('account_type');
        if (accountTypeSelect) {
            accountTypeSelect.addEventListener('change', (e) => {
                this.handleAccountTypeChange(e.target.value);
            });
        }

        // Form submission handler
        const accountForm = document.getElementById('account_form');
        if (accountForm) {
            accountForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleFormSubmission(e.target);
            });
        }
    }

    async handleAccountTypeChange(accountType) {
        const parentSelect = document.getElementById('parent_id');
        if (!parentSelect) return;

        try {
            // Clear existing options
            parentSelect.innerHTML = '<option value="">Select Parent Account (Optional)</option>';

            if (accountType) {
                // Load parent accounts for the selected type
                const parentAccounts = await this.accountManager.getParentAccountsByType(accountType);
                
                parentAccounts.forEach(account => {
                    const option = document.createElement('option');
                    option.value = account.id;
                    option.textContent = `${account.account_code} - ${account.account_name}`;
                    parentSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading parent accounts:', error);
            alert('Failed to load parent accounts. Please try again.');
        }
    }

    async handleFormSubmission(form) {
        const formData = new FormData(form);
        const accountData = {
            account_code: formData.get('account_code'),
            account_name: formData.get('account_name'),
            account_type: formData.get('account_type'),
            parent_id: formData.get('parent_id') || null,
            description: formData.get('description'),
            opening_balance: parseFloat(formData.get('opening_balance')) || 0
        };

        try {
            let result;
            if (accountData.parent_id) {
                // Creating a sub-account
                result = await this.accountManager.createSubAccount(accountData);
                alert('Sub-account created successfully!');
            } else {
                // Creating a parent account
                result = await this.accountManager.createParentAccount(accountData);
                alert('Parent account created successfully!');
            }

            // Reset form and reload page or update UI
            form.reset();
            window.location.reload(); // Or update the UI dynamically

        } catch (error) {
            console.error('Form submission error:', error);
            alert('Failed to create account: ' + error.message);
        }
    }
}

// Initialize form handler when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new AccountFormHandler();
});

