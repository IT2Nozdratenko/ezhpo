export function getTotalAll(reports, ...names) {
    let total = 0;
    for (let key in reports) {
        if (key !== 'empty_id') {
            const totalDriver = getTotal(reports[key], ...names);

            if (typeof totalDriver === 'number') {
                total += totalDriver;
            }
        } else {
            for (let type in reports.empty_id.type_auto) {
                const totalDriver = getTotal(reports.empty_id.type_auto[type], ...names)

                if (typeof totalDriver === 'number') {
                    total += totalDriver;
                }
            }
        }
    }

    if (total > 0) {
        return total;
    }

    return 'отсутствует';
}

export function getSumAll(reports, ...names) {
    let sum = 0;
    for (let key in reports) {
        const totalDriver = getSum(reports[key], ...names);

        if (typeof totalDriver === 'number') {
            sum += totalDriver;
        }
    }

    if (sum > 0) {
        return sum;
    }

    return null;
}

export function getTotal(item, ...names) {
    let total = 0;

    if (item.types) {
        for (let key in item.types) {
            names.forEach(name => {
                if (key.split('/')[0].trim().toLowerCase() === name.toLowerCase()) {
                    total += parseInt(item.types[key]?.total);
                }
            });
        }
    }

    if (total > 0) {
        return total;
    }

    return 'отсутствует';
}

export function getSum(item, ...names) {
    let sum = 0;

    if (item.types) {
        for (let key in item.types) {
            names.forEach(name => {
                if (key.split('/')[0].trim().toLowerCase() === name.toLowerCase()) {
                    sum += parseInt(item.types[key]?.sum);
                }
            });
        }
    }

    if (sum > 0) {
        return sum;
    }

    return null
}

export function getDiscount(item, ...names) {
    let disc = 0;

    if (item.types) {
        for (let key in item.types) {
            names.forEach(name => {
                if (key.split('/')[0].trim().toLowerCase() === name.toLowerCase()) {
                    disc = parseInt(item.types[key]?.discount);
                }
            });
        }
    }

    if (disc > 0) {
        return disc;
    }

    return null
}

export function isSync(item, ...names) {
    let result = false;

    if (item.types) {
        for (let key in item.types) {
            names.forEach(name => {
                if (key.split('/')[0].trim().toLowerCase() === name.toLowerCase()) {
                    result = item.types[key]?.sync;
                }
            });
        }
    }

    return result;
}
