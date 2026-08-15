
export interface HomeWorks {
    activity_id: string,
    name: string,
    requestment: string,
    files: string[],
    accept: string
}

export interface TodosCards {
    activity_id: string,
    title: string,
    name: string,
    description: string,
    start_time: string,
    deadline: string,
    is_finished: boolean
}


export interface TodosListCards {
    activity_id: string,
    title: string,
    name: string,
    description: string,
    start_time: string,
    deadline: string,
    status: boolean,
    is_finished: boolean
}

