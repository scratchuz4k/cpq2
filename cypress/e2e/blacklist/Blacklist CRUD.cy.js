const product_name = 'Software Defined FTTP';
const updated_product_name = 'Broadband FTTC MPF';
const provider = 'Virtual1'
describe("Blacklist Crud", () => {
    beforeEach(() => {
        cy.login("super@example.com", "werwerwer")
    })

    it("Create a Blacklist Item", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-products").click()
        cy.get("#blacklist-index-btn").click()
        cy.get("#blacklist-create-btn").click()

        cy.get("#item-name").type(product_name)
        cy.get("#item-provider").select("Virtual1")

        cy.get("#btn-save-item").click()
        cy.get("#datatable-search").type(product_name)
        cy.get(".p-datatable-table").contains(product_name)
    })

    it("Edit a Blacklist Item", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-products").click()
        cy.get("#blacklist-index-btn").click()
        cy.wait(5000)
        cy.get("#datatable-btn-0-edit").click()

        cy.get("#item-name").clear().type(updated_product_name)

        cy.get("#btn-save-item").click()
        cy.get("#datatable-search").type(updated_product_name)
        cy.get(".p-datatable-table").contains(updated_product_name)
    })

    it("Delete a Blacklist Item", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-products").click()
        cy.get("#blacklist-index-btn").click()

        cy.get("#datatable-search").type(updated_product_name)
        cy.get("#datatable-btn-0-delete").click()

        cy.get('.swal2-confirm').eq(0).click()
        cy.get(".notyf").contains("Removed with success")
    })
});
