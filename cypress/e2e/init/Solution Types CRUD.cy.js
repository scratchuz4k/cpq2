describe("Solution Types CRUD", () => {
    beforeEach(() => {
        cy.login("companyadmin@example.com", "werwerwer")
    })

    it("Create a Connectivity Solution", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-solutions").click()
        cy.get("#solution-create-btn").click()

        cy.get("#solution_type").click()
        cy.get(".p-select-list-container").eq(0).within(() => {
            cy.get('li[aria-label="Connectivity - Ethernet"]')
                .should('exist')
                .click();
        })

        //Change tabs
        cy.get("#1-1").click()

        cy.get("#solution_type-component").click()
        cy.get(".p-select-list-container").eq(0).within(() => {
            cy.get('li[aria-label="Simple"]')
                .should('exist')
                .click();
        })


        cy.get("#btn-save-solution-type").click()

        cy.get(".notyf").contains("Created with Success")

        cy.get("#datatable-search").type('Connectivity - Ethernet')
        cy.get(".p-datatable-table").contains('Connectivity - Ethernet')

    })

    it("Edit Connectivity Solution", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-solutions").click()

        cy.get("#datatable-search").type('Connectivity - Ethernet')
        cy.get("#datatable-btn-0-edit").click()


        cy.get("#1-1").click()

        cy.get("#solution_type-component").click()
        cy.get(".p-select-list-container").eq(0).within(() => {
            cy.get('li[aria-label="DealMaker"]')
                .should('exist')
                .click();
        })

        cy.get("#btn-save-solution-type").click()

        cy.get(".notyf").contains("Updated with Success")

        cy.get("#datatable-search").type('Connectivity - Ethernet')
        cy.get(".p-datatable-table").contains('Connectivity - Ethernet')
    })

    it("Delete Connectivity Solution", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-solutions").click()

        cy.get("#datatable-search").type('Connectivity - Ethernet')
        cy.get("#datatable-btn-0-delete").click()

        cy.get('.swal2-confirm').eq(0).click()
        cy.get(".notyf").contains("Removed with success")
    })

    it("Create a Connectivity - Ethernet Solution", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-solutions").click()
        cy.get("#solution-create-btn").click()

        cy.get("#solution_type").click()
        cy.get(".p-select-list-container").eq(0).within(() => {
            cy.get('li[aria-label="Connectivity - Ethernet"]')
                .should('exist')
                .click();
        })

        //Change tabs
        cy.get("#1-1").click()

        cy.get("#solution_type-component").click()
        cy.get(".p-select-list-container").eq(0).within(() => {
            cy.get('li[aria-label="DealMaker"]')
                .should('exist')
                .click();
        })


        cy.get("#btn-save-solution-type").click()
    })

    it("Create a Mobile Solution", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-solutions").click()
        cy.get("#solution-create-btn").click()

        cy.get("#solution_type").click()
        cy.get(".p-select-list-container").eq(0).within(() => {
            cy.get('li[aria-label="Connectivity - Ethernet"]')
                .should('exist')
                .click();
        })

        //Change tabs
        cy.get("#1-1").click()

        cy.get("#solution_type-component").click()
        cy.get(".p-select-list-container").eq(0).within(() => {
            cy.get('li[aria-label="DealMaker"]')
                .should('exist')
                .click();
        })


        cy.get("#btn-save-solution-type").click()
    })

    it("Create a Hardware Solution", () => {
        cy.visit("/")
        cy.get("#my-account").click()
        cy.get("#link-solutions").click()
        cy.get("#solution-create-btn").click()

        cy.get("#solution_type").click()
        cy.get(".p-select-list-container").eq(0).within(() => {
            cy.get('li[aria-label="Connectivity - Ethernet"]')
                .should('exist')
                .click();
        })

        //Change tabs
        cy.get("#1-1").click()

        cy.get("#solution_type-component").click()
        cy.get(".p-select-list-container").eq(0).within(() => {
            cy.get('li[aria-label="DealMaker"]')
                .should('exist')
                .click();
        })


        cy.get("#btn-save-solution-type").click()
    })
});

