const crypto = require('crypto');
const rand = (notAllowedNumber, max = 101) => {
    let randomNumber = Math.floor(Math.random() * max); // Generate a random number

    // Keep generating until it"s not equal to the not allowed number
    while (randomNumber === notAllowedNumber) {
        randomNumber = Math.floor(Math.random() * max);
    }
    return randomNumber
}
// Function to generate hash
const generateHash = (input) => {
    return crypto.createHash('md5').update(input).digest('hex'); // SHA-256 hash
}
const getCurrentDate = () => {
    const currentDate = new Date();
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
    const day = String(currentDate.getDate()).padStart(2, '0');
    const hour = String(currentDate.getHours()).padStart(2, '0');
    const minute = String(currentDate.getMinutes()).padStart(2, '0');
    const millisecond = String(currentDate.getMilliseconds()).padStart(3, '0');

    // Create a readable date string
    const readableDate = `${year}-${month}-${day} ${hour}:${minute}:${millisecond}`;

    return readableDate;
}
const temp = getCurrentDate()
const account = { ref: generateHash(temp), name: temp }

describe("Creates a Mobile Order", () => {
    beforeEach(() => {
        cy.login("companyadmin@example.com", "werwerwer")
    })
    it("Creates an Account", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#link-to-create-account").click()
        cy.get("#account-name").type(account.name)
        cy.get("#account-reference").type(account.ref)
        cy.get("#save-account-button").click()
        cy.get(".notyf").contains("Created with Success")
    })

    it("Creates Mobile Order", () => {
        cy.visit("/")
        cy.get("#create-tab").click()
        cy.get("#account_id").select(account.name)
        cy.get("#solution_id").select("Mobile")
        cy.get("#btn-create-quote").click()
        cy.get("#btn-plan-0-add-25").click()
        cy.get("#btn-plan-1-add-25").click()
        cy.get("#btn-step-1-next").click()
        cy.get("#btn-step-2-previous").click()
        cy.get("#btn-plan-2-add-50").click()
        cy.get("#btn-step-1-next").click()
        cy.get("#input-bolt-0-quantity").type(30)
        cy.get("#btn-step-2-next").click()

        cy.get("#hardware-0-stock").then(($input) => {
            const spanText = $input.text().trim(); // Get the text content from the <span> and trim any extra whitespace

            // Perform conditional logic based on the span text
            if (spanText > 0) {
                cy.get("#input-hardware-0-quantity").type(spanText)
            }
        });

        cy.get("#btn-step-3-next").click()
    })
    it("I can change Margin and Discount", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()

        let items = ["recurring", "one_off"]

        items.forEach((key) => {
            let total = null
            cy.get("#" + key + "_total").then(($title) => {
                total = $title.text().trim();
            });

            let line1 = { qty: null, discount: null, margin: null }
            cy.get("#btn-" + key + "-0-edit").click()
            cy.get("#input-" + key + "-0-quantity").then(($input) => {
                line1.qty = $input.val().trim();
            }).clear().type(rand(line1.qty));
            cy.get("#input-" + key + "-0-discount").then(($input) => {
                line1.discount = $input.val().trim();
            }).clear().type(rand(line1.discount, 11));
            cy.get("#input-" + key + "-0-margin").then(($input) => {
                line1.margin = $input.val().trim();
            }).clear().type(rand(line1.margin, 21));


            cy.get("#btn-" + key + "-0-save").click()
            cy.get("#btn-" + key + "-0-edit").click()
            cy.get("#input-" + key + "-0-quantity").invoke("val")
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.qty);
                });
            cy.get("#input-" + key + "-0-discount").invoke("val")
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.discount);
                });
            cy.get("#input-" + key + "-0-margin").invoke("val")
                .then((inputValue) => {
                    expect(inputValue).to.not.equal(line1.margin);
                });

            cy.get("#" + key + "_total").invoke("text")
                .then((text) => {
                    expect(text).to.not.equal(total);
                });
        })
    })

    it("I can change and reset Global Discount", () => {
        cy.visit("/")
        cy.get("#datatable-btn-0-show").click()
        let order = { margin: null, comission: null, global: null }

        cy.get("#input-margin-order").then(($input) => {
            order.margin = $input.val().trim();
        })
        cy.get("#input-comission_value-order").then(($input) => {
            order.comission = $input.val().trim();
        })
        cy.get("#input-global-discount").then(($input) => {
            order.global = $input.val().trim();
        }).clear().type(rand(order.global));

        cy.get("#btn-apply-global-discount").click()
        cy.get("#btn-apply-global-discount", { timeout: 3000 }).should('not.be.disabled');

        cy.get("#input-margin-order").invoke("val")
            .then((val) => {
                expect(val).to.not.equal(order.margin);
            });
        cy.get("#input-comission_value-order").invoke("val")
            .then((val) => {
                expect(val).to.not.equal(order.comission);
            });

        cy.get("#btn-reset-quote").click()
        cy.get("body > div.swal2-container.swal2-center.swal2-backdrop-show > div > div.swal2-actions > button.swal2-confirm.btn.text-light.btn-lg.mr-2.fs-6.swal2-styled.swal2-default-outline").click()
        cy.get("#btn-reset-quote", { timeout: 3000 }).should('not.be.disabled');
        cy.get("#input-margin-order").invoke("val")
            .then((val) => {
                expect(val).to.equal("0.00 %");
            });
    })
});

