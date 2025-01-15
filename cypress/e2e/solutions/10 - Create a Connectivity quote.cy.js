import { generateHash, getCurrentDate } from "../../support/utils.js";

const temp = getCurrentDate()
const account = { ref: generateHash(temp), name: temp }

describe('Creates a Connectivity quote', () => {
    beforeEach(() => {
        cy.login("super@example.com", "werwerwer")
    })

    it("Creates an Account", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type(account.name)
        cy.get("#account-reference").type(account.ref)
        cy.get("#btn-save-account").click()
        cy.get(".notyf").contains("Created with Success")
    })

    it('Create a Connectivity Quote', () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select(account.name)
        cy.get("#solution_id").select("Connectivity - Ethernet")
        cy.get("#input_address").type('EH32 9NT')
        cy.get("#select_address").should('be.visible').select(1)
        cy.get("#btn-create-quote").should('not.be.disabled').click()

        // Scroll back to top
        cy.scrollTo('top');
        //Provider filter
        cy.get('.p-multiselect').eq(0).click()

        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(0).click()
        })

        //SubProvider filter
        cy.get('.p-multiselect').eq(1).click()
        cy.wait(500)

        cy.get('.p-multiselect-list-container').eq(0).within(() => {
            cy.get('.p-checkbox').eq(1).click()
        })

        //Add products to quote
        let keys = [0, 1, 2, 3, 4]
        keys.forEach((key) => {
            cy.get('tbody tr button').eq(key).click()
            cy.get('tbody tr button').eq(key).contains('Added')
        })

        //Save quote
        cy.get('.continue-btn').eq(0).click()
        cy.get('.Recurring-item').its('length').should('eq', 5);
        cy.get('.One-Off-item').its('length').should('eq', 5);
    })
    it("I can add a Custom Line and check quoted items", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()
        cy.get("#btn-Recurring-add").click()
        cy.get("#input-new-Recurring-name").type("Custom line recurring")
        cy.get("#input-new-Recurring-qty").type(10)
        cy.get("#input-new-Recurring-price").type(10)
        cy.get("#input-new-Recurring-discount").type(10)
        cy.get("#btn-new-Recurring-add").click()
        cy.get("#table-Recurring").contains("Custom line recurring")

        cy.get("#btn-One-Off-add").click()
        cy.get("#input-new-One-Off-name").type("Custom line one_off")
        cy.get("#input-new-One-Off-qty").type(10)
        cy.get("#input-new-One-Off-price").type(10)
        cy.get("#input-new-One-Off-discount").type(10)
        cy.get("#btn-new-One-Off-add").click()
        cy.get("#table-One-Off").contains("Custom line one_off")

        cy.get("#btn-previous").click()
        cy.get("#btn-more-filters").click()
        cy.get("#filter-show-quoted").click({ force: true })
        cy.wait(1000)
        cy.get('.connectivity-table-item').its("length")
            .then((items) => {
                expect(items).to.equal(5);
            });
    })

    it.skip("I can Create an order from a quote", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-crete-order").click()
        cy.get('.swal2-confirm').eq(0).click()
        cy.get(".notyf").contains("Order created with success")
    })
})
