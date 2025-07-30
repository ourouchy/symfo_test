import axios from "axios";


const api = axios.create({
  baseURL: "/api"
});

export const getScpis = () => api.get("/scpi");
export const createScpi = (data) => api.post("/scpi", data);