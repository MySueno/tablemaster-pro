import { Router, type IRouter } from "express";
import healthRouter from "./health";
import wpUpdateRouter from "./wp-update";

const router: IRouter = Router();

router.use(healthRouter);
router.use(wpUpdateRouter);

export default router;
